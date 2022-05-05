<?php
namespace App\Helpers;

use App\Models\ClubRole;
use App\Models\ClubUser;
use App\Models\Club as ClubModal;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;

/**
 * 
 */
class Club
{
    ///检查用户是否是club的用户
    static function isClubUser($club_id,$user_id) {

        Log::debug('检查是否是club'.$club_id.'的用户');

        $club_user = ClubUser::Where([
            'user_id'   =>  $user_id,
            'club_id'   =>  $club_id,
        ]);

        if (!$club_user) {
            return false;
        }
        if ($club_user->feel == 'hate') {
            return false;
        }
        
        return true;

    }
    
    static function isClubRole($club_id,$user_id,$role) {
        $r = ClubRole::where([
            'user_id'   =>  $user_id,
            'club_id'   =>  $club_id,
            'role'      =>  $role
        ])->first();
        if ($r) {
            return true;
        }else {
            Log::debug('传入的club_id:'.$club_id);
            $c = ClubModal::find($club_id);
            if (!$c) {
                Log::debug('传入的club_id:'.$club_id.'并没有找到对应的club');
            }
            Log::info('检查club的user_id:'.$c->user_id);
            Log::info('user_id2:'.$user_id);
            if ($c && $c->user_id == $user_id) {
                return true;
            }else {
                return false;
            }
        }
    }

    static function isClubSuperAdmin($club_id,$user_id) {
        return self::isClubRole($club_id,$user_id,'super_admin');
    }
    
    static function isClubAdmin($club_id,$user_id) {
        return self::isClubRole($club_id,$user_id,'admin');
    }

    static function getRoleUserIds($club_id,$role,$use_cache = true) {

        if ($use_cache == true) {
            $query = ClubRole::cacheFor(1800)
                ->cacheTags(['club_role_list:'.$club_id.'_'.$role])
                ->select(['user_id']);
        }else {
            $query = ClubRole::select(['user_id']);
        }

        $rows = $query->where('club_id',$club_id)
            ->where('role',$role)
            ->get()
            ->toArray();

        if (is_array($rows)) {
            return Arr::pluck($rows, 'user_id');
        }else {
            return [];
        }

    }

    static function getFavClubIds($user_id) {

        ///缓存10分钟
        $fav_club_ids = Cache::remember('fav_club_ids_'.$user_id,60,function() use ($user_id){
            Log::Debug('debug,没有命中缓存:fav_club_ids_'.$user_id);
            $clubs = ClubUser::where(['user_id'=>$user_id,'is_join'=>'1'])->get();
            return $clubs->pluck('club_id');
        });

        return $fav_club_ids;
    }


    static function getRecommendClubs($category_id) {
        $lang = ClubModal::getWithLangName();
        
        ///缓存10分钟
        $ckey = sprintf("recommand_clubs_%d_%s", $category_id, $lang);
        $clubs = Cache::remember($ckey,600,function() use ($category_id){
            // Log::Debug('debug,没有命中缓存:recommend_clubs_'.$category_id);
            return ClubModal::where(['category_id'=>$category_id,'is_recommend'=>1])->withLang()->get();
        });

        return $clubs;
    }

    static function getDefaultImageUrl($path) {
        $is_default_exist = Storage::disk('local')->exists($path);
        if ($is_default_exist){
            return config('global.static_url').'/'.$path;
        }else {
            return '';
        }
    }

    static function getDefaultAvatarUrl($club_id) {
        $save_path = 'public/club_avatar/'.$club_id.'.png';
        return self::getDefaultImageUrl($save_path);
    }

    static function isAvatarFetched($club_id) {
        $save_path = 'public/club_avatar/'.$club_id.'.png';
        $url = self::getDefaultImageUrl($save_path);

        if ($url) {
            return true;
        }else {
            return false;
        }
    }

    static function getDefaultCoverUrl($club_id) {
        $save_path = 'public/club_cover/'.$club_id.'.png';
        return self::getDefaultImageUrl($save_path);
    }

    static function isCoverFetched($club_id) {
        $save_path = 'public/club_cover/'.$club_id.'.png';
        $url = self::getDefaultImageUrl($save_path);
        if ($url) {
            return true;
        }else {
            return false;
        }
    }
}