<?php

namespace App\Observers;

use App\Models\Profile;

use App\Events\AddNotification;
use App\Events\DeleteNotification;

class ProfileObserver
{

    public function creating(Profile $profile)
    {
        if (!$profile->user_id ) {
            $profile->user_id = auth('api')->user()->user_id;
        }

        return $profile;
    }

    /**
     * Handle the post "created" event.
     *
     * @param  \App\Post  $profile
     * @return void
     */
    public function created(Profile $profile)
    {

    }

    /**
     * Handle the post "updated" event.
     *
     * @param  \App\Post  $profile
     * @return void
     */
    public function updated(Profile $profile)
    {
        //
    }

    /**
     * Handle the post "deleted" event.
     *
     * @param  \App\Post  $profile
     * @return void
     */
    public function deleted(Profile $profile)
    {
    }

    /**
     * Handle the post "restored" event.
     *
     * @param  \App\Post  $profile
     * @return void
     */
    public function restored(Profile $profile)
    {
        //
    }

    /**
     * Handle the post "force deleted" event.
     *
     * @param  \App\Post  $profile
     * @return void
     */
    public function forceDeleted(Profile $profile)
    {
        //
    }
}
