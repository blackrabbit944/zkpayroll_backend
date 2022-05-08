<?php

namespace App\Policies;

use App\Models\Salary;
use App\Models\User;

// use Illuminate\Auth\Access\HandlesAuthorization;

class SalaryPolicy
{
    // use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
    }

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
     * @param  \App\Models\Salary  $salary
     * @return mixed
     */
    public function view(User $user, Salary $salary)
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
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Salary  $salary
     * @return mixed
     */
    public function update(User $user, Salary $salary)
    {
        return $user->user_id == $salary->user_id;
    }


    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Salary  $salary
     * @return mixed
     */
    public function delete(User $user, Salary $salary)
    {
        return $user->user_id == $salary->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Salary  $salary
     * @return mixed
     */
    public function restore(User $user, Salary $salary)
    {
        return $user->user_id == $salary->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Salary  $salary
     * @return mixed
     */
    public function forceDelete(User $user, Salary $salary)
    {
        return false;
    }
}
