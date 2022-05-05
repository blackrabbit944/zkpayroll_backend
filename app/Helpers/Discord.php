<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Exceptions\ProgramException;
use Illuminate\Support\Facades\Cache;

use App\Helpers\DiscordCdn;

class Discord {

    protected $client_id = '';

    function __construct() {
        $this->client_id = config('discord.bot.client_id');
        $this->client_secret = config('discord.bot.secret_key');
        $this->app_url = env('APP_URL');
    }

    function getApiUrl($url) {
        return $this->app_url . $url;
    }

    function getGuildAccessTokenByCode($code = '') {
        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded'
        ])->asForm()->post('https://discord.com/api/oauth2/token',[
            'client_id'         =>  $this->client_id,
            'client_secret'     =>  $this->client_secret,
            'grant_type'        =>  'authorization_code',
            'code'              =>  $code,
            'redirect_uri'      =>  $this->getApiUrl('/v1/discord/guild_callback'),
        ]);
        if ($response->successful()) {
            $result = $response->json();
            Log::debug('请求DiscordApi获得AccessToken如下：'.json_encode($result));
            return $result;
        }else {
            $result = $response->json();
            Log::debug('请求Discord Api 交换 CODE 报错,条件是:'.json_encode([
                'client_id'         =>  $this->client_id,
                'client_secret'     =>  $this->client_secret,
                'grant_type'        =>  'authorization_code',
                'code'              =>  $code,
                'redirect_uri'      =>  '',
            ]));
            Log::debug('请求Discord Api 交换 CODE 报错,报错是:'.json_encode($result));
            throw new ProgramException("系统错误: 请求Discord API 错误，请稍后再试");
        }
    }

    function getAccessTokenByCode($code = '') {
        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded'
        ])->asForm()->post('https://discord.com/api/oauth2/token',[
            'client_id'         =>  $this->client_id,
            'client_secret'     =>  $this->client_secret,
            'grant_type'        =>  'authorization_code',
            'code'              =>  $code,
            'redirect_uri'      =>  $this->getApiUrl('/v1/discord/user_callback'),
        ]);
        if ($response->successful()) {
            $result = $response->json();
            Log::debug('请求DiscordApi获得AccessToken如下：'.json_encode($result));
            return $result;
        }else {
            $result = $response->json();
            Log::debug('请求Discord Api 交换 CODE 报错,条件是:'.json_encode([
                'client_id'         =>  $this->client_id,
                'client_secret'     =>  $this->client_secret,
                'grant_type'        =>  'authorization_code',
                'code'              =>  $code,
                'redirect_uri'      => $this->getApiUrl('/v1/discord/user_callback')
            ]));
            Log::debug('请求Discord Api 交换 CODE 报错,报错是:'.json_encode($result));
            throw new ProgramException("系统错误: 请求Discord API 错误，请稍后再试");
        }
    }

    function _getDataByToken($url,$access_token,$data_name) {

        $api_url = 'https://discordapp.com' . $url;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$access_token 
        ])->get($api_url,[]);

        if ($response->successful()) {
            $result = $response->json();
            Log::debug('请求DiscordApi获得'.$data_name.'如下：'.json_encode($result));
            return $result;
        }else {
            $result = $response->json();
            Log::debug('请求DiscordApi去获得'.$data_name.'出错,系统返回:'.json_encode($result));
            throw new ProgramException("系统错误: 请求Discord API 错误，请稍后再试");
        }
    }

    function getUserInfo($access_token) {
        return $this->_getDataByToken('/api/users/@me',$access_token,'用户数据');
    }

    function getUserGuild($access_token) {
        return $this->_getDataByToken('/api/users/@me/guilds',$access_token,'用户加入的社区');
    }

    function getUserOwnGuild($access_token) {
        $guilds = $this->_getDataByToken('/api/users/@me/guilds',$access_token,'用户加入的社区');

        $filter_guilds = [];
        foreach($guilds as $guild) {
            if ($guild['owner']) {
                $filter_guilds[] = $guild;
            }
        }

        return $filter_guilds;
    }

    function getUserOwnGuildWithCache($access_token)
    {
        $arr = [
            'access_token' => $access_token,
        ];
        $ckey = sprintf("user_own_guild_%s", md5(json_encode($arr)));

        $guilds = Cache::remember($ckey,600,function() use ($access_token){
            return $this->getUserOwnGuild($access_token);
        });




        return $this->formatGuildList($guilds);
    }

    function formatGuildList($guilds) {
        foreach($guilds as $k => $v) {
            $guilds[$k]['avatar_url'] = DiscordCdn::GuildAvatar($v['id'],$v['icon']);
        }
        return $guilds;
    }

 }
