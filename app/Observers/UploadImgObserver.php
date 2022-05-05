<?php

namespace App\Observers;

use App\Models\UploadImg;

class UploadImgObserver
{

    /**
     * Handle the comment "creating" event.
     *
     * @param  \App\Post  $draftPost
     * @return void
     */
    public function creating(UploadImg $img)
    {
        $img->user_id = auth('api')->user()->user_id;
        return $img;
    }

    /**
     * Handle the upload img "created" event.
     *
     * @param  \App\UploadImg  $uploadImg
     * @return void
     */
    public function created(UploadImg $uploadImg)
    {
        //
    }

    /**
     * Handle the upload img "updated" event.
     *
     * @param  \App\UploadImg  $uploadImg
     * @return void
     */
    public function updated(UploadImg $uploadImg)
    {
        //
    }

    /**
     * Handle the upload img "deleted" event.
     *
     * @param  \App\UploadImg  $uploadImg
     * @return void
     */
    public function deleted(UploadImg $uploadImg)
    {
        //
    }

    /**
     * Handle the upload img "restored" event.
     *
     * @param  \App\UploadImg  $uploadImg
     * @return void
     */
    public function restored(UploadImg $uploadImg)
    {
        //
    }

    /**
     * Handle the upload img "force deleted" event.
     *
     * @param  \App\UploadImg  $uploadImg
     * @return void
     */
    public function forceDeleted(UploadImg $uploadImg)
    {
        //
    }
}
