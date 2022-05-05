<?php

namespace App\Observers;

use App\Models\UserAnalytics;

class UserAnalyticsObserver
{

    /**
     *
     * @return void
     */
    public function saving(UserAnalytics $user)
    {        
        $user->wallet_address = strtolower($user->wallet_address);
        if ($user->invite_address) {
            $user->invite_address = strtolower($user->invite_address);
        }
        return $user;
    }

}
