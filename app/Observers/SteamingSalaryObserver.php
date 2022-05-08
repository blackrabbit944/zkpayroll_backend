<?php

namespace App\Observers;

use App\Models\SteamingSalary;

use Illuminate\Support\Facades\Log;

class SteamingSalaryObserver
{

    public function creating(SteamingSalary $steamingsalary)
    {
        $steamingsalary->contract_address = strtolower($steamingsalary->contract_address);
        $steamingsalary->address = strtolower($steamingsalary->address);
        $steamingsalary->user_id = auth('api')->user()->user_id;
        return $steamingsalary;
    }

    /**
     * Handle the post "created" event.
     *
     * @return void
     */
    public function created(SteamingSalary $steamingsalary)
    {
        
    }

    /**
     * Handle the post "updated" event.
     *
     * @param  \App\Post  $steamingsalary
     * @return void
     */
    public function updated(SteamingSalary $steamingsalary)
    {
        //
    }

    /**
     * Handle the post "deleted" event.
     *
     * @param  \App\Models\SteamingSalary  $steamingsalary
     * @return void
     */
    public function deleted(SteamingSalary $steamingsalary)
    {

    }

    /**
     * Handle the post "restored" event.
     *
     * @param  \App\Post  $steamingsalary
     * @return void
     */
    public function restored(SteamingSalary $steamingsalary)
    {
        //
    }

    /**
     * Handle the post "force deleted" event.
     *
     * @param  \App\Post  $steamingsalary
     * @return void
     */
    public function forceDeleted(SteamingSalary $steamingsalary)
    {
        //
    }



}
