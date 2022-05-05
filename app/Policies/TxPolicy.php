<?php

namespace App\Policies;

use App\Models\Club;
use App\Models\User;
use App\Models\ClubUser;

use Illuminate\Support\Facades\Log;


class TxPolicy
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

        if ($data['contract_address']) {
            $club = Club::where(['contract_address'=>$data['contract_address']])->first();
            if ($club && $club->isMine()) {
                return true;  
            }
        }else if (isset($data['club_id']) && $data['club_id']) {
            $club = Club::where(['id'=>$data['club_id']])->first();
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
