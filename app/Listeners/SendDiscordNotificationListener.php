<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\AdminNotificationEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use GuzzleHttp;

use Illuminate\Support\Facades\Log;




class SendDiscordNotificationListener implements ShouldQueue
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
    public $queue = 'push_data';

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

    /**
     * Handle the event.
     *
     * @param  \App\Events\ExampleEvent  $event
     * @return void
     */
    public function handle(AdminNotificationEvent $event)
    {
        //
        $text = $event->notification['text'] ?? 'empty';
        $link = $event->notification['link'] ?? '';

        $hooks_url = '';
        switch($event->notify_type) {
            case 'normal':
                $hooks_url = config('discord.hooks.normal');
                break;
            case 'emergency':
                $hooks_url = config('discord.hooks.emergency');
                break;
            default:
                $hooks_url = '';
        }

        // Log::info('当前环境'.app()->environment());

        if (app()->environment(['local', 'staging'])) {
            Log::info('因为是本地环境，所以改用discord的测试频道');
            $hooks_url = config('discord.hooks.test');
        }
        

        if ($hooks_url) {
            Http::post($hooks_url, ['content' => $text.$link]);
        }else {
            Log::warning('不应该运行到这里，执行发送管理员通知时候发现频道没有定义');
        }

        return true;
    }
}
