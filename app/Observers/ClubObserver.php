<?php

namespace App\Observers;

use Illuminate\Support\Facades\Log;

// use App\Events\CreateClubEvent;
// use App\Models\Collection;
use App\Models\Club;

use App\Services\CollectionService;

class ClubObserver
{

    public function creating(Club $club)
    {
        if (!$club->user_id) {
            $club->user_id = auth('api')->user()->user_id;
        }

        $club->unique_hash = bin2hex(random_bytes(32));
        return $club;
    }
    /**
     * Handle the post "created" event.
     *
     * @return void
     */
    public function created(Club $club)
    {
        // Log::info('Club Observer 监听到创建club事件');
    }

    /**
     * Handle the post "updated" event.
     *
     * @param  \App\Club  $club
     * @return void
     */
    public function updated(Club $club)
    {
        //更新价格
        //
        //    
    }

    /**
     * Handle the post "deleted" event.
     *
     * @param  \App\Models\Club  $club
     * @return void
     */
    public function deleted(Club $club)
    {

    }

    /**
     * Handle the post "restored" event.
     *
     * @param  \App\Club  $club
     * @return void
     */
    public function restored(Club $club)
    {
        //
    }

    /**
     * Handle the post "force deleted" event.
     *
     * @param  \App\Club  $club
     * @return void
     */
    public function forceDeleted(Club $club)
    {
        //
    }



}
