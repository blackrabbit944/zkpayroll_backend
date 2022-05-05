<?php

namespace App\Observers;

use App\Models\DraftContent;
use Illuminate\Support\Facades\Log;

class DraftContentObserver
{
    /**
     * Handle the draft_content "saving" event.
     *
     * @param  \App\DraftContent  $draft_content
     * @return void
     */
    public function saving(DraftContent $draft_content)
    {
        if (!$draft_content->user_id) {
            $draft_content->user_id = auth('api')->user()->user_id;
        }
        return $draft_content;
    }

    /**
     * Handle the draft draft_content "created" event.
     *
     * @param  \App\DraftContent  $draft_content
     * @return void
     */
    public function created(DraftContent $draft_content)
    {   
    }

    /**
     * Handle the draft draft_content "updated" event.
     *
     * @param  \App\DraftContent  $draft_content
     * @return void
     */
    public function updated(DraftContent $draft_content)
    {
        //
    }

    /**
     * Handle the draft draft_content "deleted" event.
     *
     * @param  \App\DraftContent  $draft_content
     * @return void
     */
    public function deleted(DraftContent $draft_content)
    {
        //
    }

    /**
     * Handle the draft draft_content "restored" event.
     *
     * @param  \App\DraftContent  $draft_content
     * @return void
     */
    public function restored(DraftContent $draft_content)
    {
        //
    }

    /**
     * Handle the draft draft_content "force deleted" event.
     *
     * @param  \App\DraftContent  $draft_content
     * @return void
     */
    public function forceDeleted(DraftContent $draft_content)
    {
        //
    }
}
