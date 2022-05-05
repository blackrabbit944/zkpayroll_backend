<?php

namespace App\Observers;

use Illuminate\Support\Facades\Log;

use App\Events\CreateItemEvent;
use App\Models\Collection;
use App\Models\Item;

use App\Services\CollectionService;

class ItemObserver
{

    public function creating(Item $item)
    {
        $item->contract_address = strtolower($item->contract_address);

        $collection =  Collection::where(['contract_address'=>$item->contract_address])->first();
        if ($collection) {
            $item->collection_id = $collection->id;
        }

        return $item;
    }

    /**
     * Handle the post "created" event.
     *
     * @return void
     */
    public function created(Item $item)
    {
        // Log::info('Item Observer 监听到创建item事件');
    }

    /**
     * Handle the post "updated" event.
     *
     * @param  \App\Post  $item
     * @return void
     */
    public function updated(Item $item)
    {
        //更新价格
        //
        //    
    }

    /**
     * Handle the post "deleted" event.
     *
     * @param  \App\Models\Item  $item
     * @return void
     */
    public function deleted(Item $item)
    {

    }

    /**
     * Handle the post "restored" event.
     *
     * @param  \App\Post  $item
     * @return void
     */
    public function restored(Item $item)
    {
        //
    }

    /**
     * Handle the post "force deleted" event.
     *
     * @param  \App\Post  $item
     * @return void
     */
    public function forceDeleted(Item $item)
    {
        //
    }



}
