<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use App\Models\ClubUser;
use App\Models\Club;

use App\Helpers\Club as ClubHelper;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class PostPolicy
{
    use HandlesAuthorization;


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
     * @param  \App\Post  $post
     * @return mixed
     */
    public function view(User $user, Post $post)
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
        ///我是这个社区的管理员我才有资格写文章
        $club = Club::where([
            'id'        =>  $data['club_id'],
        ])->first();

        if ($club->user_id == $user->user_id) {
            return true;
        }else {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Post  $post
     * @return mixed
     */
    public function update(User $user, Post $post)
    {
        ///社区管理员可以管理
        if ($post->user_id == $user->user_id) {
            return true;
        }

        // $is_admin = Club::isClubAdmin($post->club_id,$user->user_id);
        // if ($is_admin) {
            // return true;
        // }
        return false;
    }


    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Post  $post
     * @return mixed
     */
    public function delete(User $user, Post $post)
    {
        ///社区管理员可以管理
         ///社区管理员可以管理
        if ($post->user_id == $user->user_id) {
            return true;
        }

        // $is_admin = Club::isClubAdmin($post->club_id,$user->user_id);
        // if ($is_admin) {
        //     return true;
        // }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Post  $post
     * @return mixed
     */
    public function restore(User $user, Post $post)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Post  $post
     * @return mixed
     */
    public function forceDelete(User $user, Post $post)
    {
        //
    }
}
