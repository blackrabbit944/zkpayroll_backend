<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\BindDiscordGuildEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use GuzzleHttp;
use App\Helpers\TinyclubGrpc;

use Illuminate\Support\Facades\Log;


class BindDiscordGuildListener implements ShouldQueue
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
    public function handle(BindDiscordGuildEvent $event)
    {
        //
        $guild = $event->getGuild();

        ///发送一个GRPC到Node去执行接下来的代码
        Log::debug('测试debug');

        Log::debug('接下来交给NodeJS执行'.json_encode($guild->toArray()));

        $tinyclubGrpcHelper = new TinyclubGrpc();
        $ret = $tinyclubGrpcHelper->initGuild($guild);


        if (!$ret) {
            Log::debug('init出错：'.$tinyclubGrpcHelper->getErrorMessage());
            return false;
        }else {
            Log::debug('init没问题');
        }
        
        return true;
    }
}
