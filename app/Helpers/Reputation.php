<?php
namespace App\Helpers;

use App\Repositories\ClubUserRepository;
use App\Repositories\VoteRepository;
use App\Helpers\Item;
use Illuminate\Support\Facades\Log;

use App\Models\ClubUser;

class Reputation
{

    public static $user_type = [
        'user'          =>  1,
        'expert_user'   =>  2
    ];

    /*
    *   获得用户的类型。
    *   我们区分用户是用用户的reputation作为标签把用户分成三类：
    *   *   新用户 
    *       - 要求：
    *           1.reputation < 200
    *   *   专家用户
    *       - 要求：
    *           1.全站的reputation >= 200
    */
    static function getUserType($user, $club_id) {

        $expert_user_gap = 200;

        ///获得这个用户在那个子站的用户
        $club_user = ClubUser::where([
            'user_id' =>  $user->user_id,
            'club_id'=>  $club_id
        ])->first();

        if ($club_user && $club_user->reputation >= $expert_user_gap) {
            return 'expert_user';
        }else {
            return 'user';
        }


    }
    /*
    *   获得用户的投票的分值
    *   
    *   按照用户类型，我们将会对数据采用一个打分的rank。
    *   这里主要考虑了2个问题
    *   
    *   1) 投票者的属性，专家和普通用户投票的权限是不一样的，专家则更能影响整个投票的价值
    *   2) 一人一票机制，公平民主。
    *   
    *   如何区别专家？
    *   1.reputation > 100 则会认为是这个领域的专家。
    *   
    *   1.一般用户
    *       - up。 +1分，
    *       - down。 -2分
    *
    *   2.专家用户。
    *       -up。 +10分
    *       -down。-20分
    *
    */

    static function getUserVoteRank($user, $item_type, $item_id , $vote_type) {

        $club_id = Item::getBelongClubId($item_type,$item_id);
        $user_type = self::getUserType($user,$club_id);

        $rank = 0;
        switch($user_type) {
            case 'expert_user':
                if ($vote_type == 'up') {
                    $rank = 10;
                }else {
                    $rank = -20;
                }
                break;
            case 'user':
                if ($vote_type == 'up') {
                    $rank = 1;
                }else {
                    $rank = -2;
                }
                break;
        }
        return $rank;

    }   


    /// 判断一个 recaptcha 分数是否过低
    /// 如果没有启用 recaptcha 功能，则无论传入任何数值，都会返回 false
    public static function isBadRecaptchaScore($s) {
        if ( env('ENABLE_REPUTATION_RECAPTCHA') == false) {
            return false;
        }

        $ret = $s < 0.5;
        Log::debug(\sprintf("当前传入的 recpathca 分数是 %s，认为这是【%s】分数", $s, $ret ? "坏" : "好"));
        return $ret;
    }




}