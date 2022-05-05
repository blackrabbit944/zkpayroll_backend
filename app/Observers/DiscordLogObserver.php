<?php

namespace App\Observers;

use Illuminate\Support\Facades\Log;

// use App\Services\DiscordLogService;
use App\Models\DiscordLog;

use App\Services\OrderService;
use App\Models\Order;
use App\Models\Collection;

use App\Events\CreateDiscordLogEvent;

class DiscordLogObserver
{

    public function creating(DiscordLog $log)
    {
        $log->status = 'init';
        Log::info('DiscordLog Observer 监听到creating事件');
    }

    /**
     * Handle the post "created" event.
     *
     * @return void
     */
    public function created(DiscordLog $log)
    {
        Log::info('DiscordLog Observer 监听到创建了条目:'.json_encode($log->toArray()));

        // 3.记录用户的行为(这里是异步触发的部分)
        event(new CreateDiscordLogEvent($log)); 
        
    }

    /**
     * Handle the post "updated" event.
     *
     * @param  \App\Post  $order
     * @return void
     */
    public function updated(DiscordLog $log)
    {
        //
    }

    /**
     * Handle the post "deleted" event.
     *
     * @param  \App\Models\DiscordLog  $order
     * @return void
     */
    public function deleted(DiscordLog $log)
    {

    }

    /**
     * Handle the post "restored" event.
     *
     * @param  \App\Post  $order
     * @return void
     */
    public function restored(DiscordLog $log)
    {
        //
    }

    /**
     * Handle the post "force deleted" event.
     *
     * @param  \App\Post  $order
     * @return void
     */
    public function forceDeleted(DiscordLog $order)
    {
        //
    }



}
