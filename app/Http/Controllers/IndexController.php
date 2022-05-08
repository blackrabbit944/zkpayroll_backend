<?php

namespace App\Http\Controllers;

use App\Events\FetchShareInfoEvent;
use App\Helpers\Notification;
use App\Helpers\Url;
use App\Models\Club;
use App\Models\User;
use App\Helpers\Ethereum;
use App\Helpers\Erc721;
use App\Helpers\TinyclubContract;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use App\Events\AddNotificationEvent;
use Illuminate\Support\Facades\Redis;

use App\Helpers\Ecrecover;
use kornrunner\Keccak;

use App\Helpers\EthSign;
use App\Helpers\Nftscan;
use App\Helpers\TinyclubNftContract;



class IndexController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function init()
    {   

        $login_user = auth('api')->user();
        // if ($user) {
        //     $login_user =  $user->toArray();
        // }else {
        //     $login_user = null;
        // }

        //移除当前的token，重新发一个token
        // $new_token = auth('api')->refresh();
        if ($login_user) {
            $new_token = auth('api')->setTTL(config('jwt.ttl'))->login($login_user);
            return $this->success([
                'login_user'            => $login_user,
                'jwt_token'             => $new_token,
            ]);

        }else {
            return $this->success([
                'login_user'    => $login_user,
                'jwt_token'     => '',
            ]);
        }

    }

    public function websiteStatus() {


        $status = Cache::remember('website_status',15,function() {

            Log::debug('debug,没有命中缓存:website_status');

            $club_count = Club::where('total_mint_income', '>' , 0)->count();
            $total_mint_income = Club::where('total_mint_income', '>' , 0)->sum('total_mint_income');

            return [
                'club_count'        =>  $club_count,
                'total_mint_income' =>  $total_mint_income,
                'time'              =>  time()
            ];

        });


        return $this->success($status);

    }

    /**
     * Home
     *
     * @param  Request  $request
     * @return Response
     */
    public function home()
    {
        echo "hello,zkpayroll";
    }

    /**
     * Home
     *
     * @param  Request  $request
     * @return Response
     */
    public function info()
    {
        phpinfo();
    }

    
    /**
     * Home
     *
     * @param  Request  $request
     * @return Response
     */
    public function test()
    {

        $tc = new TinyclubContract(); 
        $v = $tc->platformFeePPM();
        dump($v);

        // $tc = new TinyclubNftContract('0xdce4d752dccb01436c108c056b5bd1fff37022d4');
        // $data = $tc->getMintDataByCache();
        // Log::info('测试数据'.json_encode($data));


        // $lock = Cache::lock("testA",40);

        // if($lock->get()){
  
        //     Log::info('成功获得lock-test-A');
        //     sleep(30);
        //     $lock->release();
  
        //     return "success";
        // }else{
        //     Log::info('获得lock-test-A失败');
        //     return "error";
        // }  
  

        // $wallet_address = '0x5a0d4479aed030305a36a1fb516346d533e794fb';
        // $hash = substr(hash('sha256',$wallet_address.'_'.random_bytes(16)),8,16);
        // dump($hash);
        // exit;

        // $erc721Helper = new Erc721('0x5a0d4479aed030305a36a1fb516346d533e794fb');
        // // $metadata = $erc721Helper->getMetadata();
        // // dump($metadata);
 

        // $owner = $erc721Helper->ownerOf(6711);
        // dump($owner);

        // $balance = $erc721Helper->getBalanceOf('0x19f43E8B016a2d38B483aE9be67aF924740ab893');
        // dump($balance);
        
        // $helper = new Nftscan();
        // $helper->getNftStates('0xed5af388653567af2f388e6224dc7c4b3241c544');
        // $helper->getNFTs('0x19f43E8B016a2d38B483aE9be67aF924740ab893');
        // $sign = $request->input('sign');
        // $address = $request->input('address');
        // $params = $request->input('params');

        // $sign = '0x06a0e65aa208667470307688a94fd6617945123e6201825100e6e6e5db2a670b434d59a01ad973b7e2c8612b2ce57397c3e84f49f0bce3318b68638fe0fedabb1b';
        // $address = '0x374feb1050ee9f84d03be7b189a00c911fd65e2a';
        // $params = '{"domain":{"chainId":"0x2a","name":"OpenSky","version":1,"verifyingContract":"0x0000000000000000000000000000000000000000"},"message":{"create_time":1638872671,"address":"0x374fEB1050EE9F84d03BE7B189A00c911fD65e2a"},"primaryType":"Info","types":{"EIP712Domain":[{"name":"name","type":"string"},{"name":"version","type":"string"},{"name":"chainId","type":"uint256"},{"name":"verifyingContract","type":"address"}],"Info":[{"name":"create_time","type":"uint256"},{"name":"address","type":"address"}]}}';

        // $signHelper = new EthSign();
        // $ret = $signHelper->check($sign,$address,$params);
        // if (!$ret) {
        //     dump($signHelper->getErrorMessage());
        // }
        //获得数据

        ///测试mirror抓取
        // $url = 'https://mirror.xyz/0xE43a21Ee76b591fe6E479da8a8a388FCfea6F77F';
        
        // $client = new \GuzzleHttp\Client();
        // $data = $client->request('get',$url)->getBody()->getContents();

        // Log::info('请求url的结果是存在'.$data);

        // $readability = new Readability(new Configuration());
        // $readability->parse($data);

        // $data = [
        //     'asset_contract_address'=> '0x93a796b1e846567fe3577af7b7bb89f71680173a',
        //     'token_id'              => '1588',
        //     // 'account_address'       => '0xef764bac8a438e7e498c2e5fccf0f174c3e3f8db',
        // ];

        // $opensea = new \App\Helpers\Opensea;
        // $result = $opensea->isAssetBelongAddress($data,'0xef764bac8a438e7e498c2e5fccf0f174c3e3f8db');

        // $checkin = Checkin::first();
        // event(new CheckinEvent($checkin));
        // Redis::set('name', 'Taylor');
        // $values = Redis::get('name');
        // dump($values);

        //发送广播测试
        // $data = json_decode('{"from_user_id":2,"from_item_type":"comment","from_item_id":102,"to_user_id":1,"to_item_type":"post","to_item_id":"3","notify_type":"new_comment"}',true);
        // event(new AddNotificationEvent($data));

        // $mail = new ResetPasswordMail('/test');
        // 
        // Url::getHostFromUrl('https://www.figma.com/file/3Hl9MTGwOGNuwRN9v7k4SH/Web?node-id=3842%3A12682');
    }

    public function gstest() {
        return "gstest Success\n" . request()->ip();
    }
}
