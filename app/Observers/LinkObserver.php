<?php

namespace App\Observers;

use Illuminate\Support\Facades\Log;

// use App\Events\CreateLinkEvent;
// use App\Models\Collection;
use App\Models\Link;

use App\Services\CollectionService;

class LinkObserver
{

    public function creating(Link $link)
    {
        $link->user_id = auth('api')->user()->user_id;

        $count = Link::Where(['user_id'=>$link->user_id])->count();
        $link->sort_id = $count;

        return $link;
    }
    /**
     * Handle the post "created" event.
     *
     * @return void
     */
    public function created(Link $link)
    {
        // Log::info('Link Observer 监听到创建link事件');
    }

    /**
     * Handle the post "updated" event.
     *
     * @param  \App\Link  $link
     * @return void
     */
    public function updated(Link $link)
    {
        //更新价格
        //
        //    
    }

    /**
     * Handle the post "deleted" event.
     *
     * @param  \App\Models\Link  $link
     * @return void
     */
    public function deleted(Link $link)
    {

    }

    /**
     * Handle the post "restored" event.
     *
     * @param  \App\Link  $link
     * @return void
     */
    public function restored(Link $link)
    {
        //
    }

    /**
     * Handle the post "force deleted" event.
     *
     * @param  \App\Link  $link
     * @return void
     */
    public function forceDeleted(Link $link)
    {
        //
    }



}
