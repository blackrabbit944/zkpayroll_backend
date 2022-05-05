<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\CreateDiscordLogEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use GuzzleHttp;
use Carbon\Carbon;

use App\Models\DiscordLog;
use App\Models\ClubUser;
use App\Helpers\TinyclubGrpc;
use App\Services\ClubUserService;

use Illuminate\Support\Facades\Log;

class CreateDiscordLogListener implements ShouldQueue
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

    // /**
    //  * 确定监听器应该超时的时间。
    //  *
    //  * @return \DateTime
    //  */
    // public function retryUntil()
    // {
    //     Log::debug('难道是这个问题？');
    //     return Carbon::now()->addMinutes(5);
    // }


    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        Log::debug('难道是这个问题2？');
    }

    public function handleAddMember(DiscordLog $log) {
        
        ///发送一个GRPC到Node去执行接下来的代码
        Log::debug('接下来交给NodeJS执行添加角色');

        $tinyclubGrpcHelper = new TinyclubGrpc();
        return $tinyclubGrpcHelper->addRole($log->guild_id,$log->discord_user_id,'Member');

     
    }


    public function handleRemoveMember(DiscordLog $log) {
        
        ///发送一个GRPC到Node去执行接下来的代码
        Log::debug('接下来交给NodeJS执行移除角色');

        $tinyclubGrpcHelper = new TinyclubGrpc();
        return $tinyclubGrpcHelper->removeRole($log->guild_id,$log->discord_user_id,'Member');

    }

    public function handleSendNotify(DiscordLog $log) {
        
        ///发送一个GRPC到Node去执行接下来的代码
        Log::debug('请求Discord服务器进行发送消息');

        $guild = $log->discord_guild;

        if (!$guild->webhook_url) {
            Log::info(sprintf("准备给Guild:%s 发送Discord消息的时候发现他的webhook并没有初始化。",$guild->id));
            return;
        }

        Http::post($guild->webhook_url, ['content' => $log->content]);
        
        return true;
    }

    public function handleSendAdminNotify(DiscordLog $log) {
        
        ///发送一个GRPC到Node去执行接下来的代码
        Log::debug('请求Discord服务器进行发送消息');

        $guild = $log->discord_guild;

        if (!$guild->admin_webhook_url) {
            Log::info(sprintf("准备给Guild:%s 发送Discord消息的时候发现他的admin_webhook并没有初始化。",$guild->id));
            return;
        }
        Http::post($guild->admin_webhook_url, ['content' => $log->content]);
        
        return true;
    }


    /**
     * Handle the event.
     *
     * @param  \App\Events\ExampleEvent  $event
     * @return void
     */
    public function handle(CreateDiscordLogEvent $event)
    {
        Log::debug('处理discord的log事件.');

        //处理DiscordLog
        $log = $event->getLog();

        if ($log->status != 'init' && $log->status != 'failed') {
            Log::debug('处理discord_log时候，发现不是init或failed状态。log_id:'.$log->id.',状态:'.$log->status);
            return false;
        }

        $log->status = 'processing';
        $log->save();
        
        switch($log->action_type) {
            case 'add_member':
                $ret = $this->handleAddMember($log);
                ////添加add_member成功以后要把加入时间记录到club_user表中
                if ($ret) {
                    $contract_address = $log->club->contract_address;
                    $wallet_address = $log->discord_user->user->wallet_address;
                    $club_user = ClubUserService::get($contract_address,$wallet_address);
                    if ($club_user) {
                        $club_user->left_time = 0;
                        $club_user->join_time = time();
                        $club_user->save();
                    }else {
                        Log::warning('在用户添加member时候，未能发现对应的club_user,这是一个不应该发生的错误，请检查，LogId:'.$log->id);
                    }
                }
                break;
            case 'remove_member':
                $ret = $this->handleRemoveMember($log);
                ////添加add_member成功以后要把加入时间记录到club_user表中
                if ($ret) {
                    $contract_address = $log->club->contract_address;
                    $wallet_address = $log->discord_user->user->wallet_address;
                    $club_user = ClubUserService::get($contract_address,$wallet_address);
                    if ($club_user) {
                        $club_user->left_time = time();
                        $club_user->save();
                    }else {
                        Log::warning('在用户移除member时候，未能发现对应的club_user,这是一个不应该发生的错误，请检查，LogId:'.$log->id);
                    }
                }
                break;
            case 'send_notify':
                $this->handleSendNotify($log);
                break;
            case 'send_admin_notify':
                $this->handleSendAdminNotify($log);
                break; 

            default:
                Log::error('遇到了不能处理的DiscordLog，类型是:'.$log->action_type);
        }

        ///更新log
        $log->status = 'success';
        $log->success_time = time();
        $log->save();

        return true;
    }


    /**
     * 处理失败任务。
     *
     * @param  \App\Events\CreateDiscordLogEvent  $event
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(CreateDiscordLogEvent $event, $exception) {

        $log = $event->getLog();
        Log::debug('Discord事件失败了，事件ID是:'.$log->id);

        $log->status = 'failed';
        $log->save();
    }

}
