<?php

namespace App\Observers;

use App\Models\Post;
use App\Models\Feed;
use App\Models\ClubUser;
use App\Models\ReputationLog;

use App\Events\RemoveNotificationEvent;

class PostObserver
{

    public function creating(Post $post)
    {   
        if (!$post->user_id ) {
            $post->user_id = auth('api')->user()->user_id;
        }
        // $post->autoFillLang();
        return $post;
    }

    /**
     * Handle the post "created" event.
     *
     * @param  \App\Post  $post
     * @return void
     */
    public function created(Post $post)
    {

        //同时创建一条feed记录
        // $feed = [
        //     'club_id'       =>  $post->club_id,
        //     'item_id'       =>  $post->post_id,
        //     'item_type'     =>  'post',
        //     'user_id'       =>  $post->user_id,
        // ];
        // Feed::create($feed);

        // $this->createReputation($post);
        
        /// 创建一条通知
        // $club = $post->club;
        // $text = sprintf('A new post was asked by a user in the club "%2$s": %1$s', $post->title, $club->name); 

        // event(new AddNotification([
        //     'from_user_id'  =>  auth('api')->user()->user_id,
        //     'to_user_id'    =>  $club->user_id,
        //     'item_id'       =>  $post->post_id,
        //     'item_type'     =>  'club_post',
        //     'noti_type'     =>  'club_post_create',
        //     'body'          =>  $text,
        //     'xid'           =>  "club_user_ask_new_post_{$post->post_id}",
        // ]));


    }

    /**
     * Handle the post "updated" event.
     *
     * @param  \App\Post  $post
     * @return void
     */
    public function updated(Post $post)
    {
        //
    }

    /**
     * Handle the post "deleted" event.
     *
     * @param  \App\Post  $post
     * @return void
     */
    public function deleted(Post $post)
    {
        // if ($post->feed) {
        //     $post->feed->delete();
        // }

        // event(new RemoveNotificationEvent([
        //     'to_item_type'=>  'post',
        //     'to_item_id'  =>  $post->post_id,
        // ]));

        // $this->revertReputation($post);

    }

    /**
     * Handle the post "restored" event.
     *
     * @param  \App\Post  $post
     * @return void
     */
    public function restored(Post $post)
    {
        //
    }

    /**
     * Handle the post "force deleted" event.
     *
     * @param  \App\Post  $post
     * @return void
     */
    public function forceDeleted(Post $post)
    {
        //
    }

    // public function createReputation(Post $post) {
    //     $club_user_id = ClubUser::getClubUserID($post->club->club_id, $post->user_id, true);
    //     ReputationLog::add('add_post', $post->post_id, $post->user_id, $club_user_id, 'post', $post->post_id);
    // }

    /// 当一个投票被删除的时候，对应的 reputation 记录也应该被删除
    // public function revertReputation(Post $post) {
    //     $cond = [
    //         'verb' => 'add_post',
    //         'verb_id' => $post->post_id,
    //     ];
    //     $log = ReputationLog::where($cond)->first();
    //     if ($log) {
    //         $log->delete();
    //     }
    // }
}
