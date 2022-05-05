<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\CheckNftHolderEvent;
use Illuminate\Queue\InteractsWithQueue;
// use Illuminate\Support\Facades\Http;
// use GuzzleHttp;
use Carbon\Carbon;

use App\Helpers\Erc721;
use Illuminate\Support\Facades\Log;

use App\Services\ClubUserService;
use App\Models\Club;

class CheckNftHolderListener implements ShouldQueue
{

    /**
     * 任务将被发送到的连接的名称
     *
     * @var string|null
     */
    public $connection = 'database';

    /**
     * 任务将被发送到的队列的名称
     *
     * @var string|null
     */
    public $queue = 'discord';

    /**
     * 任务被处理的延迟时间（秒）
     *
     * @var int
     */
    public $delay = 0;

    /**
     * 尝试队列监听器的次数
     *
     * @var int
     */
    public $tries = 3;

    /**
     * 确定监听器应该超时的时间。
     */
    public function retryUntil()
    {
        return Carbon::now()->addMinutes(5);
    }

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getClubUser($contract_address,$address) {
        return ClubUserService::get($contract_address,$address);
    }
    /**
     * Handle the event.
     *
     * @param  \App\Events\ExampleEvent  $event
     * @return void
     */
    public function handle(CheckNftHolderEvent $event)
    {
        
        Log::debug('触发了CheckNftHolderListener');

        //处理DiscordLog
        $contract_address = $event->getContractAddress();
        $address = $event->getAddress();

        $club = Club::where(['contract_address'=>$contract_address])->first();
        if (!$club) {
            Log::debug('合约没有对应的club,contract_address'.$contract_address);
            return false;
        }

        $user = $this->getClubUser($contract_address,$address);

        Log::debug('获得用户是：'.json_encode($user));

        //利用合约去获得数据
        try {

            //从链上数据获得用户持有NFT数量，balanceOf
            $erc721Helper = new Erc721($contract_address);
            $nft_balance = $erc721Helper->getBalanceOf($address);

        }catch (Exception $e) {

            Log::debug('获得用户持有的NFT数量出错,contract_address'.$contract_address.'，报错:'.$e->getMessage());

            ///标记用户更新失败
            $user->status = 'failed';
            $user->save();
        }

        if ($nft_balance > 0) {
            //如果用户没有join过（新创建的记录，或者用户以前已经left了这个社区）
            //更新:
            //  1.这里应该任何情况下都需要调用一次，因为用户可能已经手动离开了这个社区又加入进去以后没有了权限，重新验证时，这时候需要再次触发这个逻辑
            //  2.再次发送这个开销也并不是很大，和相比去GRPC检查用户是否已经加入了这个社区，都是一次开销，并且还省去了GRPC给出用户是否已经是member的接口
            $user->joinClub($nft_balance);
        }else {
            $user->leftClub();
        }

        return true;
    }


    /**
     * 处理失败任务。
     *
     * @param  \App\Events\CheckNftHolderEvent  $event
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(CheckNftHolderEvent $event, $exception) {

        $contract_address = $event->getContractAddress();
        $address = $event->getAddress();
        
        Log::debug('Discord事件失败了，contract_address是:'.$contract_address.','.'用户地址是:'.$address);

        $user = $this->getClubUser($contract_address,$address);
        $user->status = 'failed';
        $user->save();

    }
}
