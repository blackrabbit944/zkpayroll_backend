<?php

namespace App\Helpers;

use Tinyclub\InitDiscordGuildRequest;
use Tinyclub\TinyclubGrpcClient;

use Tinyclub\RemoveDiscordRoleRequest;
use Tinyclub\AddDiscordRoleRequest;
use Tinyclub\UpdateChannelNameRequest;
use Tinyclub\GetInviteLinkRequest;


use Illuminate\Support\Facades\Log;

use App\Models\DiscordGuild;
use App\Models\DiscordUser;

use Ethsign\CloseskySignRequest;
use Ethsign\CloseskySignReply;

use App\Models\Traits\ErrorMessage;

class TinyclubGrpc {

    use ErrorMessage;

    protected $grpc_client;

    function getGrpcClient() {
        if (!$this->grpc_client) {
            $host = config('grpc.host').':'.config('grpc.port');
            $this->grpc_client = new TinyclubGrpcClient($host,[
                'credentials' => \Grpc\ChannelCredentials::createInsecure(),
            ]);
        }
        return $this->grpc_client;
    }

    function initGuild(DiscordGuild $guild)
    {
        if (!$guild->club) {
            $this->setErrorMessage('club is empty or not exist');
            return false;
        }

        if (!$guild->club->unique_name) {
            $this->setErrorMessage('club unique_name is empty or not exist');
            return false;
        }

        // if ($guild->is_init) {
        //     $this->setErrorMessage('guild is inited yet');
        //     return false;
        // }

        $client = $this->getGrpcClient();


        $request = new InitDiscordGuildRequest();
        $request->setGuildId($guild->guild_id);
        $request->setClubUniqueName($guild->club->unique_name);

        $call = $client->InitDiscordGuild($request);
        
        list($response, $status) = $call->wait();

        if ($response->getStatus() != 'success') {
            Log::warning('可能Discord社区有问题并没有完成Init，需要关注一下');
            $this->setErrorMessage('discord guild is not inited, something goes wrong.');
            return false;
        }else {
            Log::debug('init完成了');
        }

        ////需要把现在这个社区的数据放进来
        $webhook = $response->getNotifyWebhook();
        if ($webhook) {
            $guild->webhook_token = $webhook->getToken();
            $guild->webhook_id = $webhook->getId();
        }

        $admin_webhook = $response->getAdminNotifyWebhook();
        if ($admin_webhook) {
            $guild->admin_webhook_token = $admin_webhook->getToken();
            $guild->admin_webhook_id = $admin_webhook->getId();
        }

        $guild->save();

        $guild->is_init = 1;
        $guild->save();

    }

    function getInviteLink(DiscordGuild $guild) {

        $client = $this->getGrpcClient();

        $request = new GetInviteLinkRequest();
        $request->setGuildId($guild->guild_id);

        $call = $client->GetInviteLink($request);

        list($response, $status) = $call->wait();

        if ($response->getStatus() != 'success') {
            Log::warning('获得invitelink有问题');
            $this->setErrorMessage('get discord invite link error, something goes wrong.');
            return false;
        }else {
            Log::debug('获得invitelink完成');
        }
        return $response->getInviteLink();
    }

    function updateChannel(DiscordGuild $guild,$data) {
        $client = $this->getGrpcClient();

        $request = new UpdateChannelNameRequest();
        $request->setGuildId($guild->guild_id);
        $request->setDataString(json_encode($data));

        $call = $client->UpdateChannelName($request);

        list($response, $status) = $call->wait();

        if ($response->getStatus() != 'success') {
            Log::warning('更新channel数据有问题，需要关注一下');
            $this->setErrorMessage('discord channel is not update success, something goes wrong.');
            return false;
        }else {
            Log::debug('更新channel数据完成');
        }

        return true;
    }

    function addRole($guild_id,$discord_user_id,$role_name) {

        $client = $this->getGrpcClient();

        $request = new AddDiscordRoleRequest();
        $request->setGuildId($guild_id);
        $request->setUserId($discord_user_id);
        $request->setRoleName($role_name);

        $call = $client->AddDiscordRole($request);

        list($response, $status) = $call->wait();

        if ($response->getStatus() != 'success') {
            Log::warning('添加用户角色有问题，需要关注一下');
            $this->setErrorMessage('add discord role is not success, something goes wrong.');
            return false;
        }else {
            Log::debug('添加用户角色完成');
        }

        return true;
    }

    function removeRole($guild_id,$discord_user_id,$role_name) {

        $client = $this->getGrpcClient();

        $request = new RemoveDiscordRoleRequest();
        $request->setGuildId($guild_id);
        $request->setUserId($discord_user_id);
        $request->setRoleName($role_name);

        $call = $client->RemoveDiscordRole($request);

        list($response, $status) = $call->wait();

        if ($response->getStatus() != 'success') {
            Log::warning('移除用户角色有问题，需要关注一下');
            $this->setErrorMessage('remove discord role is not success, something goes wrong.');
            return false;
        }else {
            Log::debug('移除用户角色完成');
        }

        return true;
    }


    // function getOrderSignature($contract_address,$token_id,$from_address,$to_address,$price,$fee,$deadline) {
        
    //     $host = config('grpc.host').':'.config('grpc.port');

    //     $client = new EthsignCheckClient($host,[
    //         'credentials' => \Grpc\ChannelCredentials::createInsecure(),
    //     ]);


    //     $request = new CloseskySignRequest();
    //     $request->setContractAddress($contract_address);
    //     $request->setTokenId($token_id);
    //     $request->setSeller($from_address);
    //     $request->setBuyer($to_address);
    //     $request->setPriceEther($price);
    //     $request->setFeeEther($fee);
    //     $request->setDeadline($deadline);

    //     $call = $client->GetCloseskySignature($request);
    //     list($response, $status) = $call->wait();

    //     if ($response->getStatus() == 'success') {
    //         return $response->getSignature();
    //     }else {
    //         return false;
    //     }
    // }




}
