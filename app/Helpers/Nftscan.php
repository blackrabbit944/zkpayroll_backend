<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use App\Exceptions\ProgramException;

class Nftscan {

    public $api_id = '';
    public $api_secret = '';

    function __construct() {
        $this->api_id = env('NFTSCAN_API_ID');
        $this->api_secret = env('NFTSCAN_API_SECRET');
    }

    function _getAccessToken() {
        $url = 'https://restapi.nftscan.com/gw/token';

        Log::debug('请求获得accesstoken，条件是:'.json_encode([
            'apiKey'        =>  $this->api_id,
            'apiSecret'     =>  $this->api_secret,
        ]));

        $response = Http::get($url,[
            'apiKey'        =>  $this->api_id,
            'apiSecret'     =>  $this->api_secret,
        ]);
        if ($response->successful()) {
            return $response->json();
        }else {
            Log::info('请求Nftscan Api报错.');
            throw new ProgramException("系统错误: 请求NFT API 错误，请稍后再试");
        }
    }

    function getAccessToken() {
        $api_id = $this->api_id;
        $api_secret = $this->api_secret;
        $accessToken = Cache::remember('nftscan_api_access_token',7000,function() {
            Log::debug('debug,没有命中缓存:nftscan_api_access_token');
            $data = $this->_getAccessToken();
            return $data['data']['accessToken'];
        });

        Log::debug('debug，拿到的access_token是'.$accessToken);
        return $accessToken;
    }



    private function _getBaseUrl() {
        return 'https://restapi.nftscan.com';
    }

    private function getUrl($url) {
        return $this->_getBaseUrl() . $url;
    }

    function getNftStates($contract_address) {

        $url = $this->getUrl('/api/v1/getStates');

        $cond = [
            'nft_address' =>  $contract_address
        ];

        $response = Http::withHeaders([
            'Access-Token' => $this->getAccessToken()
        ])->post($url,$cond);

        if ($response->successful()) {
            Log::info('请求Nftscan Api成功，条件是:'.json_encode($cond));
            return $response->json();
        }else {
            Log::info('请求Nftscan Api报错,条件是:'.json_encode($cond));
            // dump($url);
            // dump($response->json());
            throw new ProgramException("系统错误: 请求NFT API 错误，请稍后再试");
        }
    }

    function getNftInfotmation($contract_address) {

        $url = $this->getUrl('/api/v1/getNftPlatformInformation');

        $cond = [
            'nft_address' =>  $contract_address
        ];

        $response = Http::withHeaders([
            'Access-Token' => $this->getAccessToken()
        ])->post($url,$cond);

        if ($response->successful()) {
            Log::info('请求Nftscan Api成功，条件是:'.json_encode($cond));
            return $response->json();
        }else {
            Log::info('请求Nftscan Api报错,条件是:'.json_encode($cond));
            dump($url);
            dump($response->json());
            throw new ProgramException("系统错误: 请求NFT API 错误，请稍后再试");
        }
    }

    function getNFTs($owner_address,$filter_contract_addresses = []) {

        $url = $this->getUrl('/api/v1/getGroupByNftContract');

        $cond = [
            'erc'   =>  'erc721',
            'user_address' =>  $owner_address
        ];


        $response = Http::withHeaders([
            'Access-Token' => $this->getAccessToken()
        ])->post($url,$cond);

        if ($response->successful()) {
            Log::info('请求Nftscan Api成功，条件是:'.json_encode($cond));
            Log::info('请求Nftscan Api成功，结果是:'.json_encode($response->json()));
            return $response->json();
        }else {
            Log::info('请求Nftscan Api报错,条件是:'.json_encode($cond));
            dump($url);
            dump($response->json());
            throw new ProgramException("系统错误: 请求NFT API 错误，请稍后再试");
        }
    }

    public function getNFTsByCache($owner_address,$filter_contract_addresses = [],$page_key = '') {

        $arr = [
            'owner_address' => $owner_address,
            'filter_contract_addresses'  =>  implode(',',$filter_contract_addresses),
            'page_key'      => $page_key
        ];
        $ckey = sprintf("alchemy_getnfts_%s", md5(json_encode($arr)));

        $assets = Cache::remember($ckey,600,function() use ($owner_address,$filter_contract_addresses,$page_key){
            // Log::Debug('debug,没有命中缓存:recommend_clubs_'.$category_id);
            return $this->getNFTs($owner_address,$filter_contract_addresses,$page_key);
        });

        return $assets;

    }




 }
