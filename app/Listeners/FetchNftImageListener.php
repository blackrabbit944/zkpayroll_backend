<?php

namespace App\Listeners;

use App\Events\CreateItemEvent;
use App\Models\Notification;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Intervention\Image\Facades\Image;

use App\Helpers\Image as ImageHelper;

class FetchNftImageListener implements ShouldQueue
{

    /**
     * 任务将被发送到的连接的名称
     *
     * @var string|null
     */
    // public $connection = 'redis';

    /**
     * 最大尝试次数
     *
     * @var int
     */
    public $tries = 3;

    /**
     * 任务将被发送到的队列的名称
     *
     * @var string|null
     */
    public $queue = 'fetch_data';

    /**
     * 任务被处理的延迟时间（秒）
     *
     * @var int
     */
    public $delay = 0;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    function getPath($contract_name,$token_id,$width,$extension) {
        return 'public/nft_image/'.$contract_name.'/'.$width.'/'.$token_id.'.'.$extension;
    }


    // function getExtension($mime) {
    //     if ($mime == 'image/jpeg')
    //         $extension = 'jpg';
    //     elseif ($mime == 'image/png')
    //         $extension = 'png';
    //     elseif ($mime == 'image/gif')
    //         $extension = 'gif';
    //     else
    //         $extension = '';
    //     return $extension;
    // }
 

    /**
     * Handle the event.
     *
     * @param  \App\Events\CreateItemEvent  $event
     * @return void
     */
    public function handle(CreateItemEvent $event)
    {

        // if (app()->environment('testing')) {
        //     return true;
        // }
        
        $item = $event->getItem();
        Log::info('自动抓取头像,avatar:'.json_encode($item->toArray()));

        ///获取头像逻辑
        /**
         *  1.解析IPFS或HTTPS的地址，IPFS转换为HTTPS地址
         *  2.创建一个NFT的图片地址，把图片存储到本地的这个地址，并做一次200*200的像素的压缩。
         *  3.在nft_avatar中更新这个图片的本地地址
         */
        
        //1
        //
        //
        if ($item->local_path != '') {
            Log::debug('准备获得NFT图片时发现本地存储了local_path:'.$item->local_path.',id是:'.$item->id);
            return true;
        }
        
        $image_url = $item->getImageUrlHttp();

        Log::info('NFT_AVATAR:准备从远端获得图片数据:'.$image_url);

        try {
            
            $client = new \GuzzleHttp\Client(['verify' => false]);
            $data = $client->request('get',$image_url)->getBody()->getContents();

            Log::info('NFT_AVATAR:准备从远端获得图片数据完成');
            $size = config('nft.save_image_size');
            $img = Image::make($data);
            $mime = $img->mime();
            $extension = ImageHelper::getExtension($mime);

            //按照宽度压缩
            $img->widen($size,function ($constraint) {
                $constraint->upsize();
            });

            // $img->fit($size,$size);

            $save_path = $this->getPath($item->contract_address,$item->token_id,$size,$extension);
            Log::info('获得以后保存的位置:'.$save_path);

            $ret = Storage::disk('local')->put($save_path, $img->encode($extension,90));

            Log::info('保存结果:'.$ret);

            if ($ret) {

                //保存到数据库
                $item->local_path =  $save_path;
                $item->save();

                return $save_path;
            }else {
                return false;
            }


        } catch (\GuzzleHttp\RequestException $e) {

            Log::info('报错了:'.$e->messages());
            echo 'fetch fail';
        }
        // $client = new Client(['verify' => false]);  //忽略SSL错误


    }

    /**
     * 处理任务的失败
     *
     * @param  \App\Events\OrderShipped  $event
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(CreateItemEvent $event, $exception)
    {

        $item = $event->getItem();
        Log::info('[队列失败]自动抓取NFT图片失败,item:'.json_encode($item->toArray()));

    }


}
