<?php

namespace App\Services;

use App\Models\DiscordGuild;
use App\Models\DiscordUser;
use App\Models\User;
use App\Models\Club;

use App\Helpers\Discord;
use App\Helpers\DiscordCdn;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

use App\Exceptions\ProgramException;
use App\Exceptions\ApiException;

use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Helpers\Image as ImageHelper;

use App\Models\Traits\ErrorMessage;

use App\Events\BindDiscordGuildEvent;

class DiscordService 
{
    use ErrorMessage;


    /*
    *   绑定Discord用户并存储Access等信息到我们数据库中
    *   params @code 从discord拿到的基础的code数据
    *   params @state 从discord拿到的基础的state
    */
    public function bindGuild($code,$state) {

        ///1.如果这个社区已经绑定过Discord账号了，则不允许，会报一个已经绑定的错误
        $guild = DiscordGuild::where(['club_id'=>$state])->first();
        if ($guild) {
            $this->setErrorMessage('club has already bind discord group yet');
            return false;
        }

        //判断club是否存在
        $club = Club::where(['id'=>$state])->first();
        if (!$club) {
            $this->setErrorMessage('club has not created yet');
            return false;
        }


        ///2.通过code拿到用户的DiscordAccessToken等数据，并写入数据库
        $discordHelper = new Discord();
        $access_data = $discordHelper->getGuildAccessTokenByCode($code);
        if (!$access_data) {
            $this->setErrorMessage('get guild access_data error');
            return false;
        }
        
        $guild = DiscordGuild::where(['guild_id'=>$access_data['guild']['id']])->first();

        if (!$guild) {
            $this->setErrorMessage('get guild error');
            return false;
        }

        if ($guild && $guild->club_id) {
            $this->setErrorMessage('this guild is bind to other club yet.');
            return false;
        }

        ///验证这个guild对应的用户和club对应的用户是同一个人
        if ($guild->discord_user->user_id != $club->user_id) {
            Log::warning("discord guild is belongs to user:".$guild->discord_user->user_id.",and club is belongs to user:".$club->user_id." ,it is not equals");
            Log::warning(sprintf("discord guild is belongs to user:%d,and club is belongs to user:%d ,it is not equals",$guild->discord_user->user_id,$club->user_id));
            $this->setErrorMessage('discord guild user is not the club owner');
            return false;
        }

        Log::debug(sprintf('准备把guild绑定到club:%d上',$club->id));

        $guild->club_id = $club->id;
        $guild->save();


        ///3.发一个绑定完成事件，未来会依靠nodejs来执行一些初始化的工作
        event(new BindDiscordGuildEvent($guild));
        
        return true;
    }

    /*
    *   绑定Discord用户并存储Access等信息到我们数据库中
    *   params @code 从discord拿到的基础的code数据
    *   params @state 从discord拿到的基础的state
    */
    public function bindUser($code,$state) {

        ///1.如果这个用户已经绑定过Discord账号了，则不允许，会报一个已经绑定的错误
        
            ///1.1获得需要绑定的用户，必须存在
            $user = User::where(['unique_hash'=>$state])->first();
            if (!$user) {
                $this->setErrorMessage('user unknow');
                return false;
            }

            ///1.2检查这个用户是否已经绑定过discord用户
            if ($user->discord_user) {
                $this->setErrorMessage('user has already bind discord user yet');
                return false;
            }

        ///2.通过code拿到用户的DiscordAccessToken等数据，并写入数据库
        $discordHelper = new Discord();
        $access_data = $discordHelper->getAccessTokenByCode($code);

        if (!is_array($access_data) || !$access_data['access_token']) {
            $this->setErrorMessage('access_token fetch error');
            return false;
        }

        ///3.拿到用户基础数据，写入数据库
        $user_info = $discordHelper->getUserInfo($access_data['access_token']);

        ///3.1要确定这个discord用户没有绑定给别人，否则要报错
            $check_discord_user = discordUser::where(['discord_user_id'=>$user_info['id']])->first();
            if ($check_discord_user) {
                $this->setErrorMessage('The discord user has been bound to user :'.$check_discord_user->user->wallet_address);
                return false;
            }

        ///3.2创建discord用户数据库
        $discord_user = discordUser::create([
            'user_id'       =>  $user->user_id,
            'access_token'  =>  $access_data['access_token'],
            'refresh_token' =>  $access_data['refresh_token'],
            'token_expire_time' =>  time() + $access_data['expires_in'],
            'discord_user_id'   =>  $user_info['id'],
            'name'   =>  $user_info['username'],
            'avatar'    =>  $user_info['avatar'],
            'discriminator' =>  $user_info['discriminator'],
            'email'         =>  $user_info['email']
        ]);



        return $discord_user;

        ///4.完成

    }

    public  function saveGuild($guild) {
        $row = DiscordGuild::where(['guild_id' =>  $guild['guild_id']])->first();
        if (!$row) {
            $row = DiscordGuild::create($guild);
        }else {
            Log::debug('更新'.json_encode($guild));
            $row->fill($guild)->save();
        }
        return $row;
    }


 
}
