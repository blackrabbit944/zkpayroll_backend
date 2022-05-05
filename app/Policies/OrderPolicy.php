<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;


class OrderPolicy
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
     * @param  \App\Models\Order  $collection
     * @return mixed
     */
    public function view(User $user, Order $collection)
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
        Log::debug('创建记录检查用户是否是所有者,user:'.$user->wallet_address.',data:'.json_encode($data));
        if (strtolower($user->wallet_address) == strtolower($data['wallet_address'])) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Order  $order
     * @return mixed
     */
    public function update(User $user, Order $order)
    {
        Log::debug('创建记录检查用户是否是所有者,user:'.$user->wallet_address.',data:'.json_encode($order->from_address));
        if (strtolower($user->wallet_address) == strtolower($order->from_address)) {
            return true;
        }
        return false;
    }


    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Order  $collection
     * @return mixed
     */
    public function delete(User $user, Order $order)
    {
        if (strtolower($user->wallet_address) == strtolower($order->from_address)) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Order  $collection
     * @return mixed
     */
    public function restore(User $user, Order $collection)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Order  $collection
     * @return mixed
     */
    public function forceDelete(User $user, Order $collection)
    {
        return false;
    }
}
