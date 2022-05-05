<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\SendDiscordMessageEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use GuzzleHttp;

use Illuminate\Support\Facades\Log;


class SendDiscordMessageListener implements ShouldQueue
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
    public function handle(SendDiscordMessageEvent $event)
    {
        //
        $guild = $event->getGuild();
        $message = $event->getMessage();
        $link = $event->getLink();

        if (!$guild->webhook_url) {
            Log::info(sprintf("准备给Guild:%s 发送Discord消息的时候发现他的webhook并没有初始化。",$guild->id));
            return;
        }

        Http::post($guild->webhook_url, ['content' => $message.$link]);

        return true;
    }
}
