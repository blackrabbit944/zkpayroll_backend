<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{

    /**
     *
     * @return void
     */
    public function creating(User $user)
    {        
        $user->wallet_address = strtolower($user->wallet_address);
        $user->unique_hash = substr(hash('sha256',$user->wallet_address.'_'.random_bytes(16)),8,16);

        return $user;
    }

}
