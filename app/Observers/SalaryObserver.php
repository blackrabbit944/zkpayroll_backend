<?php

namespace App\Observers;

use App\Models\Salary;

use Illuminate\Support\Facades\Log;

class SalaryObserver
{

    public function creating(Salary $salary)
    {
        $salary->contract_address = strtolower($salary->contract_address);
        $salary->address = strtolower($salary->address);
        $salary->user_id = auth('api')->user()->user_id;
        return $salary;
    }

    /**
     * Handle the post "created" event.
     *
     * @return void
     */
    public function created(Salary $salary)
    {
        
    }

    /**
     * Handle the post "updated" event.
     *
     * @param  \App\Post  $salary
     * @return void
     */
    public function updated(Salary $salary)
    {
        //
    }

    /**
     * Handle the post "deleted" event.
     *
     * @param  \App\Models\Salary  $salary
     * @return void
     */
    public function deleted(Salary $salary)
    {

    }

    /**
     * Handle the post "restored" event.
     *
     * @param  \App\Post  $salary
     * @return void
     */
    public function restored(Salary $salary)
    {
        //
    }

    /**
     * Handle the post "force deleted" event.
     *
     * @param  \App\Post  $salary
     * @return void
     */
    public function forceDeleted(Salary $salary)
    {
        //
    }



}
