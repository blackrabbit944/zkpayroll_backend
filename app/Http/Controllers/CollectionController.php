<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Collection;

use App\Helpers\Collection as CollectionHelper;

use App\Http\Requests\CollectionRequest;
use App\Services\CollectionService;

// use App\Events\FetchTokenInfoEvent;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Gate;

use Carbon\Carbon;


class CollectionController extends Controller
{

    public function add(CollectionRequest $request) {

        $attributes = $request->only([
            'contract_address',
        ]);

        if ($request->user()->cannot('create', [Collection::class,$attributes])) {
            return $this->failed('you have no access for add collection');
        }

        $collection = Collection::withTrashed()->where([
            'contract_address'     =>  $request->input('contract_address'),
        ])->first();

        
        if (!$collection) {
            $collectionService = new CollectionService();
            $collection = $collectionService->create($attributes);
        }elseif ($collection->trashed()) {
            $collection->restore();
        }

        if ($collection) {
            $collection->refresh();
            return $this->success($collection);
        }else {
            return $this->failure('报错了');
        }
 
    }

    public function HotList(CollectionRequest $request) {
        $page_size = get_page_size($request);
        $collection = Collection::where([
            'is_verify'     =>  1,
        ])->paginate($page_size);

        return $this->success($collection);
    }

    public function delete(CollectionRequest $request) {

        $collection = Collection::where([
            'contract_address'     =>  $request->input('contract_address'),
        ])->first();

        if (!$collection) {
            return $this->failed('collection is not exist');
        }

        if ($request->user()->cannot('delete', $collection)) {
            return $this->failed('you have no access for delete collection');
        }

        $ret = $collection->delete();

        return $this->success([]);
    }

    public function update(CollectionRequest $request) {


        $attributes = $request->only([
            'name',
            'avatar_img_id',
            'cover_img_id',
            'is_verify',
            'chain',
            'eip_type',
            'symbol',
            'item_count',
            'discord_link',
            'twitter_link',
            'website_link',
            'instagram_link'
        ]);

        $collection = Collection::find($request->input('id'));

        if (!$collection) {
            return $this->failed('collection is not exist');
        }

        if ($request->user()->cannot('update', $collection)) {
            return $this->failed('you have no access for update collection');
        }

        $collection->fill($attributes);
        $collection->save();

        $collection->fresh();
        $collection->format();

        return $this->success($collection);
    }



    public function load(CollectionRequest $request) {

        $collection = Collection::where([
            'contract_address'  =>  $request->input('contract_address')
        ])->first();

        if (!$collection) {
            return $this->failed('collection is not exist');
        }

        $collection->format();

        return $this->success($collection);
    }


    public function list(CollectionRequest $request) {

        $page_size = get_page_size($request);
        $order = get_order_by($request);

        if ($request->input('keyword')) {
            $data = Collection::search($request->input('keyword'))->paginate($page_size);
        }else {
            $cond = [];

            if ($request->input('is_verify')) {
                $cond['is_verify'] = 1;
            }

            $data = Collection::where($cond)->orderby($order[0],$order[1])->paginate($page_size);
        }


        $data->getCollection()->transform(function ($value) {
            $value->format();
            return $value;
        });

        return $this->success($data);

    }




}
