<?php

use App\Models\User;

use Illuminate\Support\Facades\DB;
use App\Events\CreateItemEvent;
use App\Listeners\FetchNftImageListener;
use App\Models\Item;

use App\Services\ItemService;

class NftControllerTest extends TestCase
{



    /** @test */
    public function f_nft_fetch_event() {
        
        //测试NFT的下载事件是否成功
        $item = Item::factory()->create([
            'contract_address'  =>  '0xed5af388653567af2f388e6224dc7c4b3241c544',
            'token_id'          =>  1111,
            'image_url'         =>  'https://ikzttp.mypinata.cloud/ipfs/QmYDvPAXtiJg7s8JdRBSLWdgSphQdac8j1YuQNNxcGE1hg/1111.png'
        ]);

        $event = new CreateItemEvent($item);
        $listener = new FetchNftImageListener();
        $file_path = $listener->handle($event);

        //断言这个张图片被下载并且被存储
        Storage::disk('local')->assertExists($file_path);

        //访问实时压缩图片的地址，获得图片是正确的
        

        //删除这个图片
        Storage::disk('local')->delete($file_path);

    }

    /** @test */
    public function f_nft_resize_event() {
        
        //测试NFT的下载事件是否成功
        $item = Item::factory()->create([
            'contract_address'  =>  '0xed5af388653567af2f388e6224dc7c4b3241c544',
            'token_id'          =>  1111,
            'image_url'         =>  'https://ikzttp.mypinata.cloud/ipfs/QmYDvPAXtiJg7s8JdRBSLWdgSphQdac8j1YuQNNxcGE1hg/1111.png'
        ]);

        $event = new CreateItemEvent($item);
        $listener = new FetchNftImageListener();
        $file_path = $listener->handle($event);

        //断言这个张图片被下载并且被存储
        Storage::disk('local')->assertExists($file_path);

        //访问实时压缩图片的地址，获得图片是正确的
        ItemService::getImageBySize([
            'contract_address'  =>  '0xed5af388653567af2f388e6224dc7c4b3241c544',
            'token_id'          =>  1111,
        ],200);

        //删除这个图片
        Storage::disk('local')->delete($file_path);

    }

    /** @test */
    public function f_nft_resize_webp_event() {
        
        //测试NFT的下载事件是否成功
        $item = Item::factory()->create([
            'contract_address'  =>  '0xb285adcd956f41cf77bf45f5143c0a9305f7a224',
            'token_id'          =>  519,
            'image_url'         =>  'ipfs://QmQDzK62g5eKtpbKrrWgHxH1yLfioR6t356aXLLLF9XPDJ/519.webp'
        ]);

        $event = new CreateItemEvent($item);
        $listener = new FetchNftImageListener();
        $file_path = $listener->handle($event);

        //断言这个张图片被下载并且被存储
        Storage::disk('local')->assertExists($file_path);

        //访问实时压缩图片的地址，获得图片是正确的
        ItemService::getImageBySize([
            'contract_address'  =>  '0xb285adcd956f41cf77bf45f5143c0a9305f7a224',
            'token_id'          =>  519,
        ],200);

        //删除这个图片
        // Storage::disk('local')->delete($file_path);

    }
}

