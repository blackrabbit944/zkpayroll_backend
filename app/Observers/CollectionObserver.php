<?php

namespace App\Observers;

use App\Models\Collection;

use Illuminate\Support\Facades\Log;

class CollectionObserver
{

    public function creating(Collection $collection)
    {
        $collection->contract_address = strtolower($collection->contract_address);
        return $collection;
    }

    /**
     * Handle the post "created" event.
     *
     * @return void
     */
    public function created(Collection $collection)
    {
        
    }

    /**
     * Handle the post "updated" event.
     *
     * @param  \App\Post  $collection
     * @return void
     */
    public function updated(Collection $collection)
    {
        //
    }

    /**
     * Handle the post "deleted" event.
     *
     * @param  \App\Models\Collection  $collection
     * @return void
     */
    public function deleted(Collection $collection)
    {

    }

    /**
     * Handle the post "restored" event.
     *
     * @param  \App\Post  $collection
     * @return void
     */
    public function restored(Collection $collection)
    {
        //
    }

    /**
     * Handle the post "force deleted" event.
     *
     * @param  \App\Post  $collection
     * @return void
     */
    public function forceDeleted(Collection $collection)
    {
        //
    }



}
