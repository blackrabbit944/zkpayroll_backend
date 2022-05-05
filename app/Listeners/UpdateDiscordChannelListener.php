<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\UpdateDiscordChannelEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use GuzzleHttp;

use Illuminate\Support\Facades\Log;

use App\Helpers\TinyclubGrpc;

class UpdateDiscordChannelListener implements ShouldQueue
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
    public function handle(UpdateDiscordChannelEvent $event)
    {
        Log::debug('触发UpdateDiscordChannelListener');
        
        $guild = $event->getGuild();
        $data = $event->getData();

        $tinyclubGrpcHelper = new TinyclubGrpc();
        $ret = $tinyclubGrpcHelper->updateChannel($guild,$data);

        return true;
    }
}
