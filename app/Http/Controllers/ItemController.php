<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Category;

use App\Helpers\Item as ItemHelper;
use App\Helpers\Alchemy;

use App\Http\Requests\ItemRequest;

use App\Services\ItemService;

use App\Events\FetchTokenInfoEvent;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Collection;

use Illuminate\Http\Response;

use Carbon\Carbon;


class ItemController extends Controller
{

    public function add(ItemRequest $request) {

        $attributes = $request->only([
            'contract_address',
            'token_id'
        ]);

        if ($request->user()->cannot('create', [Item::class,$attributes])) {
            return $this->failed('you have no access for add item');
        }

        $itemService = new ItemService();
        $item = $itemService->create($attributes);

        if ($item) {
            $item->refresh();
            return $this->success($item);
        }else {
            return $this->failure('报错了');
        }
 
    }

    public function image(ItemRequest $request) {

        $attributes = $request->only([
            'contract_address',
            'token_id',
            'width'
        ]);

        $item = Item::where([
            'contract_address'     =>  $request->input('contract_address'),
            'token_id'             =>  $request->input('token_id'),
        ])->first();

        
        if (!$item) {
            return $this->failure('item is not exist');
        }

        //针对svg图片做一次判断
        if (substr($item->local_path,-4) == '.svg') {
            $content = Storage::disk('local')->get($item->local_path);
            return response($content)
                ->header('Content-Type', 'image/svg+xml');
        }

        $image_path = ItemService::getImageBySize([
            'contract_address'     =>  $request->input('contract_address'),
            'token_id'             =>  $request->input('token_id'),
        ],$request->input('width'));

        if (!$image_path) {
            return $this->failure('item is not exist');
        }


        $content = Storage::disk('local')->get($image_path);

        $img = Image::make($content);
        $mime = $img->mime();

        return response($content)
            ->header('Content-Type', $mime);

    }

    // public function delete(ItemRequest $request) {

    //     $item = Item::where([
    //         'contract_address'     =>  $request->input('contract_address'),
    //     ])->first();

    //     if (!$item) {
    //         return $this->failed('item is not exist');
    //     }

    //     if ($request->user()->cannot('delete', $item)) {
    //         return $this->failed('you have no access for delete item');
    //     }

    //     $ret = $item->delete();

    //     return $this->success([]);
    // }

    public function update(ItemRequest $request) {


        $attributes = $request->only([
            'image_url',
        ]);

        $item = Item::find($request->input('id'));

        if (!$item) {
            return $this->failed('item is not exist');
        }

        if ($request->user()->cannot('update', $item)) {
            return $this->failed('you have no access for update item');
        }

        $item->fill($attributes);
        $item->save();

        $item->fresh();
        // $item->format();

        return $this->success($item);
    }



    public function load(ItemRequest $request) {

        if ($request->input('id')) {
            $item = Item::find($request->input('id'));
        }else {
            $item = Item::where([
                'contract_address'  =>  $request->input('contract_address'),
                'token_id'          =>  $request->input('token_id'),
            ])->first();
        }

        if (!$item) {
            return $this->failed('item is not exist');
        }
        
        $item->format();
        // $item->append('');

        return $this->success($item);
    }


    public function myList(ItemRequest $request) {


        $page_size = get_page_size($request);
        $order = get_order_by($request);

        $wallet_address = auth('api')->user()->wallet_address;
        // $wallet_address = $request->input('wallet_address');

        ///1.从Alchemy拿到用户所持有的NFT
        $alchemyHelper = new Alchemy();
        $result = $alchemyHelper->getNFTsByCache($wallet_address,[],'',true);

        if ($result) {
            Log::debug('从alchemy获得数据是：'.json_encode($result));
        }else {
            Log::debug('从alchemy获得数据是空：'.$result);
        }

        ///2.把NFT写入Item中（自动开启抓取NFT图片的event）
        $itemService = new ItemService();

        $item_list = new Collection();
        if ($result && $result['ownedNfts']) {
            foreach ($result['ownedNfts'] as $nft) {

                ///判断是不是721能不能创建
                $eip_type = $nft['id']['tokenMetadata']['tokenType'];

                if ($eip_type == 'ERC721') {


                    if (in_array($nft['contract']['address'],['0x57f1887a8bf19b14fc0df6fd9b2acc9af147ea85'])) {
                        continue;
                    }

                    $item_data = [
                        'contract_address' => $nft['contract']['address'],
                        'token_id'         => hexdec($nft['id']['tokenId']),
                        'metadata'         => $nft['metadata'],
                        'owner_address'    => $wallet_address
                    ];
                    $item = $itemService->create($item_data);

                    if ($item) {
                        $item->format();
                        $item_list->push($item);
                    }


                }else {

                    Log::debug('因为查询到的nft不是erc721而是：'.$eip_type.'，所以放弃创建');

                }

            }
        }

        ///
        ///3.返回用户持有的NFT列表
        return $this->success([
            'list'      =>  $item_list,
            'page_key'  =>  (isset($result['pageKey'])) ? $result['pageKey'] : ''
        ]);


        // $data = Collection::where([
        //     'item'  =>  $cond
        // ])->orderby($order[0],$order[1])->paginate($page_size);

        // if ($request->input('keyword')) {
        //     $data = It::search($request->input('keyword'))->paginate($page_size);
        // }else {
        //     $cond = [];

        //     if ($request->input('is_verify')) {
        //         $cond['is_verify'] = 1;
        //     }

        // }


        // $data->getCollection()->transform(function ($value) {
        //     $value->format();
        //     return $value;
        // });

    }

    public function list(ItemRequest $request) {

        $page_size = get_page_size($request);
        $order_by = get_order_by($request);


        $cond=[];
        if ($request->input('has_order')) {
            $cond[] = ['expire_time','>',time()];  
        }
        if ($request->input('contract_address')) {
            $cond['contract_address'] = $request->input('contract_address');  
        }

        $data = Item::where($cond)->orderby($order_by[0],$order_by[1])->paginate($page_size);


        $data->getCollection()->transform(function ($value) {
            $value->format();
            return $value;
        });

        return $this->success($data);

    }



}
