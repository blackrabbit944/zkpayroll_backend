<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Exceptions\ProgramException;


class DiscordCdn {

    public static $base_url = 'https://cdn.discordapp.com';

    static function GetDefaultUserAvatar() {
        return env('STATIC_URL').'/img/default.svg';
    }

    static function UserAvatar($user_id,$user_avatar) {

        if (!$user_avatar) {
            return self::GetDefaultUserAvatar();
        }

        return sprintf('%s/avatars/%s/%s.png',
            self::$base_url,
            $user_id,
            $user_avatar
        );
    }

    static function GuildAvatar($guild_id,$guild_icon) {
        //icons/guild_id/guild_icon.png *   
        return sprintf('%s/icons/%s/%s.png',
            self::$base_url,
            $guild_id,
            $guild_icon
        );
    }

 }
