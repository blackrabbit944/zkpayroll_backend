<?php

namespace App\Services;

use App\Models\Item;
use App\Helpers\Moralis;
use App\Helpers\Alchemy;

use App\Events\CreateItemEvent;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

use App\Exceptions\ProgramException;
use App\Exceptions\ApiException;

use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Helpers\Image as ImageHelper;

class ItemService 
{

    static private function getPath($contract_name,$token_id,$width,$extension) {
        return 'public/nft_image/'.$contract_name.'/'.$width.'/'.$token_id.'.'.$extension;
    }

    static private function isWebImage($img) {
        $string = substr($img, 0, 7);

        switch(substr($img, 0, 7)) {
            case 'https:/':
            case 'http://':
            case 'ipfs://':
                return true;
        }

        return false;
    }

    static private function dealImage($img,$data) {

        Log::debug('发现了一个不属于url的Image');
        Log::debug($img);

        $size = config('nft.save_image_size');

        if (stripos($img,'data:image/svg') === 0) {
            Log::debug('图片是svg图片');

            $img = substr($img,strlen('data:image/svg+xml;base64,'));
            $img = base64_decode($img);

            $save_path = self::getPath($data['contract_address'],$data['token_id'],$size,'svg');
            Log::debug('获得以后保存的位置:'.$save_path);
            $ret = Storage::disk('local')->put($save_path, $img);
            Log::debug('保存结果:'.$ret);
            return $save_path;
        }else {
            return '';
        }
    }

    static public function create($data = []) {

        Log::debug('创建Item被触发');

        $item = Item::withTrashed()->where([
            'contract_address'     =>  $data['contract_address'],
            'token_id'             =>  $data['token_id'],
        ])->first();

        if ($item) {
            if ($item->trashed()) {
                $item->restore();
            }
            return $item;
        }

        ///从Collection先创建一个Collectoon
        $collection = CollectionService::create([
            'contract_address'  =>  $data['contract_address']
        ]);

        if (!$collection) {
            throw new ProgramException('Collection无法被创建');
            return;
        }


        if (!isset($data['metadata']) || !$data['metadata']) {
            Log::debug('准备从Alchemy获得Metadata');

            $alchemyHelper = new Alchemy();
            $metadata_result = $alchemyHelper->getNFTMetadata($data['contract_address'],$data['token_id']);
            $metadata = $metadata_result['metadata'];
        }else {
            $metadata = $data['metadata'];
        }

        Log::debug('获得metadata是:'.json_encode($metadata));

        if (!isset($metadata['image'])) {
            Log::debug('创建的时候发现没有metadata的image，放弃创建:'.json_encode($metadata));
            Log::debug('创建数据是:'.json_encode($data));
            return false;
        }


        $save_data = [
            'token_id'          =>  $data['token_id'],
            'contract_address'  =>  $data['contract_address'],
            'image_url'         =>  '',
            'local_path'        =>  '',
            'data'              =>  json_encode($metadata),
            'owner_address'     =>  isset($data['owner_address']) ? $data['owner_address'] : ""
        ];



        ///因为有一些NFT直接把image的数据存储到了nft中，这时候就需要单独做这个处理
        $is_web_img = self::isWebImage($metadata['image']);
        if ($is_web_img) {
            $save_data['image_url'] = $metadata['image'];
        }else {
            $local_path = self::dealImage($metadata['image'],$data);
            $save_data['local_path'] = $local_path;
        }

        Log::debug('准备从Alchemy获得Metadata处理以后得到:'.json_encode($save_data));

        $item = Item::create($save_data);

        Log::debug('准备触发CreateItemEvent');

        ///开启一个事件下载这个头像
        event(new CreateItemEvent($item));

        return $item;


    }


    static function getResizePath($contract_name,$token_id,$width,$extension) {
        return 'public/nft_image/'.$contract_name.'/'.$width.'/'.$token_id.'.'.$extension;
    }


    static public function getImageBySize($data,$size) {

        $item = Item::where([
            'contract_address'       => $data['contract_address'],
            'token_id'               => $data['token_id']
        ])->first();

        if (!$item || !$item->local_path) {
            Log::debug('访问实时压缩图片时候，发现这个item没有被存储，条件是:'.json_encode($data).',size:'.$size);
            return false;
        } 

        ///判断size是否在允许范围内
        $size_allowed = config('nft.allow_preview_size');
        
        if (!in_array($size,$size_allowed)) {
            Log::debug('访问实时压缩图片时候，发现size不在许可范围内:'.json_encode($data).',size:'.$size);
            return false;
        }



        ///载入图片，实时压缩
        $content = Storage::disk('local')->get($item->local_path);
        Log::debug('载入文件success');

        $img = Image::make($content);
        $mime = $img->mime();
        $extension = ImageHelper::getExtension($mime);
        // dump($extension);

        ///判断压缩的图片是否存在了
        $save_path = self::getResizePath($item->contract_address,$item->token_id,$size,$extension);
        $ret = Storage::disk('local')->exists($save_path);

        if ($ret) {
            Log::info('访问实时压缩图片时候，发现图片已经存在了:'.$save_path);
            return $save_path;
        }


        //按照宽度压缩
        $img->widen($size,function ($constraint) {
            $constraint->upsize();
        });


        Log::debug('获得以后保存的位置:'.$save_path);

        $ret = Storage::disk('local')->put($save_path, $img->encode($extension,90));

        if ($ret) {
            Log::debug('保存成功');
            return $save_path;
        }

        return $ret;



    }


}
