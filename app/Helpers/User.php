<?php
namespace App\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * 
 */
class User
{

    static function getDefaultImageUrl($path) {

        return config('global.static_url').'/img/default.svg';

        $is_default_exist = Storage::disk('local')->exists($path);
        if ($is_default_exist){
            return config('global.static_url').'/'.$path;
        }else {
            return '';
        }
    }

    static function getDefaultAvatarUrl($user_id) {
        $save_path = 'public/user_avatar/'.$user_id.'.png';
        return self::getDefaultImageUrl($save_path);
    }

    static function isAvatarFetched($user_id) {
        $save_path = 'public/user_avatar/'.$user_id.'.png';
        $url = self::getDefaultImageUrl($save_path);

        if ($url) {
            return true;
        }else {
            return false;
        }
    }

}