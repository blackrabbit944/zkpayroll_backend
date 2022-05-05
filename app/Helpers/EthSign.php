<?php

namespace App\Helpers;

use Tinyclub\CheckSignRequest;
use Tinyclub\TinyclubGrpcClient;
use Illuminate\Support\Facades\Log;
use App\Models\Traits\ErrorMessage;

use App\Models\Sign;


class EthSign {

    use ErrorMessage;
    // $sign = '0x06a0e65aa208667470307688a94fd6617945123e6201825100e6e6e5db2a670b434d59a01ad973b7e2c8612b2ce57397c3e84f49f0bce3318b68638fe0fedabb1b';
    // $address = '0x374feb1050ee9f84d03be7b189a00c911fd65e2a';
    // $params = '{"domain":{"chainId":"0x61","name":"Login JIANDA","version":1,"verifyingContract":"0x0000000000000000000000000000000000000000"},"message":{"create_time":1638872671,"address":"0x374fEB1050EE9F84d03BE7B189A00c911fD65e2a"},"primaryType":"Info","types":{"EIP712Domain":[{"name":"name","type":"string"},{"name":"version","type":"string"},{"name":"chainId","type":"uint256"},{"name":"verifyingContract","type":"address"}],"Info":[{"name":"create_time","type":"uint256"},{"name":"address","type":"address"}]}}';

    protected $time_gap = 300;

    public function getMessage($params) {
        $p = json_decode($params,true);
        return $p['message'];
    }

    function verifyParams($params,$action_name) {

        try {
            $params_arr = json_decode($params,true);
        }catch(Exception $error) {
            $this->setErrorMessage('params is not a json string');
            return false;
        }


        if (hexdec($params_arr['domain']['chainId']) != env('ETH_CHAIN_ID')) {
            $this->setErrorMessage(sprintf('chainId is not allowed, only allowed: %d, input: %d',env('ETH_CHAIN_ID'),hexdec($params_arr['domain']['chainId']) ));
            return false;
        }

        if ($params_arr['domain']['name'] != env('APP_NAME')) {
            $this->setErrorMessage('domain name is not allowed, only allowed: '.env('APP_NAME'));
            return false;
        }

        if (!$params_arr['message']['wallet_address']) {
            $this->setErrorMessage('wallet_address is not allowed, input: '.$params_arr['message']['wallet_address']);
            return false;
        }

        if ($params_arr['message']['action_name'] != $action_name) {
            $this->setErrorMessage('action_name is not allowed, input: '.$params_arr['message']['action_name']);
            return false;
        }

        ///时间偏差必须小于300秒
        if (!app()->environment('local')) {
            $time_gap = abs(time() - $params_arr['message']['create_time']);
            if ($time_gap > $this->time_gap) {
                $this->setErrorMessage(sprintf('time is not allowed, input: %d , Maximum deviation of %d seconds , checked this deviation is %d seconds',$params_arr['message']['create_time'],$this->time_gap,$time_gap));
                return false;
            }
        }

       

        $sign = Sign::where(['wallet_address'=>strtolower($params_arr['message']['wallet_address'])])->orderBy('id','desc')->first();

        if ($sign && $sign->sign_unixtime > $params_arr['message']['create_time']) {
            $this->setErrorMessage(sprintf('sign time is less then last sign_unixtime, last : %d , this time: %d',$sign->sign_unixtime,$params_arr['message']['create_time']));
            return false;
        }

        return true;
    }


    function check($sign, $address, $params , $action_name = null)
    {
        $ret = $this->verifyParams($params, $action_name);

        if (!$ret) {
            Log::debug('验证params出错直接被拒绝');
            return false;
        }




        
        $host = config('grpc.host').':'.config('grpc.port');

        $client = new TinyclubGrpcClient($host,[
            'credentials' => \Grpc\ChannelCredentials::createInsecure(),
        ]);

        $request = new CheckSignRequest();
        $request->setSign($sign);
        $request->setWalletAddress($address);
        $request->setSignParams($params);

        $call = $client->CheckSign($request);
        
        list($response, $status) = $call->wait();

        Log::debug('验证签名status'.json_encode($status));
        Log::debug('验证签名response'.json_encode($response));

        // dump($response);
        // dump($status);
        // dump($response->getMessage());

        if ($response && $response->getMessage() == 'success') {

            $params_arr = json_decode($params,true);
            ////记录最后一次成功时间
            Sign::create([
                'wallet_address'    =>  strtolower($address),
                'sign_unixtime'     =>  $params_arr['message']['create_time']
            ]);

            return true;
        }else {
            $this->setErrorMessage('signature is incorrect');
            return false;
        }

    }





}
