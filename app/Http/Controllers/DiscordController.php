<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Club;
use App\Models\DiscordGuild;
use App\Models\DiscordUser;
use App\Models\DiscordLog;

use App\Exceptions\ProgramException;
use App\Http\Requests\DiscordRequest;
use App\Helpers\Discord;
use App\Helpers\TinyclubGrpc;
use App\Helpers\Erc721;
use App\Events\CheckNftHolderEvent;

use App\Events\NftHolderCheckerEvent;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

use App\Services\DiscordService;

use App\Listeners\CheckNftHolderListener;

// use Carbon\Carbon;


class DiscordController extends Controller
{

    public function userCallback(DiscordRequest $request) {


        $code = $request->input('code');
        $state = $request->input('state');

        if ($code && $state) {

            $DiscordService = new DiscordService();
            $ret = $DiscordService->bindUser($code,$state);
            

            if ($ret) {
                ///需要关闭窗口
                return view('tinyclub.success', ['title' => 'bind success!']);
                // return $this->success('bind successful');
            }else {
                return view('tinyclub.error', ['title' => $DiscordService->getErrorMessage()]);
                // return $this->failed('bind error :'.$DiscordService->getErrorMessage());
            }
        }

    }


    public function guildCallback(DiscordRequest $request) {


        $code = $request->input('code');
        $state = $request->input('state');
        $guild_id = $request->input('guild_id');
        $permissions = $request->input('permissions');

        // if ($permissions != '8') {
        //     return $this->failed('bind error permissions is not correct:');
        // }

        if ($code && $state) {

            $DiscordService = new DiscordService();
            $ret = $DiscordService->bindGuild($code,$state);

            if ($ret) {
                ///需要关闭窗口
                return view('tinyclub.success', ['title' => 'bind success!']);
                // return $this->success('bind successful');
            }else {
                return view('tinyclub.error', ['title' => $DiscordService->getErrorMessage()]);
            }
        }

    }


    public function getClubInfo(DiscordRequest $request) {

        //这个接口只允许登陆用户访问
        $discord_user = auth('api')->user()->discord_user;

        $club_id = $request->input('club_id');
        $club = Club::find($club_id);

        $discord_guild = $club->discord_guild;

        // $data = [];
        // if ($discord_user && $discord_user->token_expire_time > time()) {
        //     $discordHelper = new Discord();
        //     $data = $discordHelper->getUserOwnGuildWithCache($discord_user->access_token);
        // }

        return [
            'discord_guild' =>  $discord_guild,
            'discord_user'  =>  $discord_user,
        ];

    }


    public function getUserOwnGuilds(DiscordRequest $request) {

        //这个接口只允许登陆用户访问
        $discord_user = auth('api')->user()->discord_user;

        if (!$discord_user) {
            return $this->failed('have not connect discord yet');
        }

        if ($discord_user && $discord_user->token_expire_time < time()) {
            return $this->failed('discord connect is expired , please reconnect discord');
        }

        $discordHelper = new Discord();
        // if (app()->environment('local')) {
            // $guilds = $discordHelper->getUserOwnGuildWithCache($discord_user->access_token);
        // }else {
            $guilds = $discordHelper->getUserOwnGuild($discord_user->access_token);
        // }
        $guilds = $discordHelper->formatGuildList($guilds);

        $DiscordService = new DiscordService();

        ///把数据写入数据库
        foreach($guilds as $guild) {
            $DiscordService->saveGuild([
                'discord_user_id'   =>  $discord_user->discord_user_id,
                'guild_id'          =>  $guild['id'],
                'icon'              =>  $guild['icon'],
                'name'              =>  $guild['name'],
            ]);
        }

        return $guilds;

    }

    public function verifyNft(DiscordRequest $request) {
        
        $discord_user = auth('api')->user()->discord_user;

        if (!$discord_user) {
            return $this->failed('You are not yet bound to a Discord account');
        }

        $club_id = $request->input('club_id');
        $club = Club::where(['id'=>$club_id])->first();
        if (!$club) {
            return $this->failed('club is not exist');
        }        
        if (!$club->discord_guild) {
            return $this->failed('club is not bind a discord server yet');
        }
        if (!$club->contract_address) {
            return $this->failed('club is not bind a contract address yet');
        }
        
        ///1.检查我是否拥有NFT了
        try {
            //从链上数据获得用户持有NFT数量，balanceOf
            $erc721Helper = new Erc721($club->contract_address);
            $nft_balance = $erc721Helper->getBalanceOf(auth('api')->user()->wallet_address);

        }catch (Exception $e) {
            Log::debug('获得用户持有的NFT数量出错,contract_address'.$contract_address.'，报错:'.$e->getMessage());
        }

        if ($nft_balance > 0) {
            Log::debug('触发了检查用户持有NFT的后续流程');
            Log::debug('contract_address'.$club->contract_address);
            Log::debug('wallet_address'.auth('api')->user()->wallet_address);

            event(new CheckNftHolderEvent($club->contract_address,auth('api')->user()->wallet_address));
        }
        
        return $this->success([
            'nft_balance'   =>  $nft_balance,
        ]);
    }

    /*
    *   因为在用户绑定discord时候，用户可能尚未加入一些Discord的社区，这时候无法给予用户member权限，
    *   因此需要一个接口，用户手动触发时候去给用户添加Member的状态
    */
    public function verifyUser(DiscordRequest $request) {

        $club_id = $request->input('club_id');
        $discord_user = auth('api')->user()->discord_user;

        if (!$discord_user) {
            return $this->failed('have not connect discord yet');
        }

        $club = Club::where(['id'=>$club_id])->first();
        if (!$club || !$club->contract_address) {
            Log::debug('club is not exist or unbind contract_address yet');
            return false;
        }

        //利用合约去获得数据
        try {
            //从链上数据获得用户持有NFT数量，balanceOf
            $erc721Helper = new Erc721($club->contract_address);
            $nft_balance = $erc721Helper->getBalanceOf(auth('api')->user()->wallet_address);

        }catch (Exception $e) {
            Log::debug('获得用户持有的NFT数量出错,contract_address'.$contract_address.'，报错:'.$e->getMessage());
        }

        if ($nft_balance > 0) {
            event(new CheckNftHolderEvent($club->contract_address,auth('api')->user()->wallet_address));
        }
        
        return $this->success([
            'nft_balance'   =>  $nft_balance,
        ]);
        
    }

    public function getBindUser(DiscordRequest $request) {
        $discord_user = auth('api')->user()->discord_user;
        return [
            'discord_user'  =>  $discord_user,
        ];
    }

   
    public function test(DiscordRequest $request) {

        $contract_address = '0xdce4d752dccb01436c108c056b5bd1fff37022d4';
        $wallet_address = '0x05e6959423ffb22e04d873bb1013268aa34e24b8';

        Log::debug('准备触发CheckNftHolderEvent');
        $event = new CheckNftHolderEvent($contract_address,$wallet_address );
        event($event);

        Log::debug('触发完成');

    }




}
