<?php

namespace App\Observers;

use Illuminate\Support\Facades\Log;

use App\Events\CreateSignEvent;
use App\Models\Collection;
use App\Models\Sign;

use App\Services\CollectionService;

class SignObserver
{

    public function creating(Sign $sign)
    {
        $sign->wallet_address = strtolower($sign->wallet_address);

        return $sign;
    }

    /**
     * Handle the post "created" event.
     *
     * @return void
     */
    public function created(Sign $sign)
    {
        // Log::info('Sign Observer 监听到创建sign事件');
    }

    /**
     * Handle the post "updated" event.
     *
     * @param  \App\Post  $sign
     * @return void
     */
    public function updated(Sign $sign)
    {
        //更新价格
        //
        //    
    }

    /**
     * Handle the post "deleted" event.
     *
     * @param  \App\Models\Sign  $sign
     * @return void
     */
    public function deleted(Sign $sign)
    {

    }

    /**
     * Handle the post "restored" event.
     *
     * @param  \App\Post  $sign
     * @return void
     */
    public function restored(Sign $sign)
    {
        //
    }

    /**
     * Handle the post "force deleted" event.
     *
     * @param  \App\Post  $sign
     * @return void
     */
    public function forceDeleted(Sign $sign)
    {
        //
    }



}
