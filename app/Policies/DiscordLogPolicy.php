<?php

namespace App\Policies;

use App\Models\Club;
use App\Models\User;
use App\Models\ClubUser;

use Illuminate\Support\Facades\Log;


class DiscordLogPolicy
{

    // public function before(User $user, $ability)
    // {
    //     return false;
    // }


    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user, array $data)
    {

        if ($data['club_id']) {
            $club = Club::find($data['club_id']);
            if ($club && $club->isMine()) {
                return true;  
            }
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ClubUser  $club
     * @return mixed
     */
    public function view(User $user, ClubUser $club_user)
    {
        if ($club_user->wallet_address == $user->wallet_address) {
            return true;
        }
        if ($club_user->club->isMine()) {
            return true;
        }
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user, array $data)
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Club  $club
     * @return mixed
     */
    public function update(User $user, Club $club)
    {
        return false;
    }


    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Club  $club
     * @return mixed
     */
    public function delete(User $user, Club $club)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Club  $clu b
     * @return mixed
     */
    public function restore(User $user, Club $club)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Club  $club
     * @return mixed
     */
    public function forceDelete(User $user, Club $club)
    {
        return false;
    }
}
