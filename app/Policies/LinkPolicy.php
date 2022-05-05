<?php

namespace App\Policies;

use App\Models\Link;
use App\Models\User;
use Illuminate\Support\Facades\Log;


class LinkPolicy
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
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Link  $link
     * @return mixed
     */
    public function view(User $user, Link $link)
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
        if ($user) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Link  $link
     * @return mixed
     */
    public function update(User $user, Link $link)
    {
        Log::debug('创建记录检查用户是否是所有者,user:'.$user->user_id.',data:'.json_encode($link->user_id));
        if ($user->user_id == $link->user_id) {
            return true;
        }
        return false;
    }


    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Link  $link
     * @return mixed
     */
    public function delete(User $user, Link $link)
    {
        if ($user->user_id == $link->user_id) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Link  $link
     * @return mixed
     */
    public function restore(User $user, Link $link)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Link  $link
     * @return mixed
     */
    public function forceDelete(User $user, Link $link)
    {
        return false;
    }
}
