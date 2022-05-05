<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use App\Exceptions\ProgramException;

class Alchemy {

    public $api_key = '';

    function __construct() {
        $this->api_key = env('ALCHEMY_API_KEY');
    }

    private function _getBaseUrl() {
        return 'https://eth-mainnet.g.alchemy.com/v2/' . $this->api_key;
    }

    private function getUrl($url) {
        return $this->_getBaseUrl() . $url;
    }

    private function getRequestString($cond) {
        $string_arr = [];
        foreach($cond as $key => $value) {
            if (is_array($value)) {
                foreach($value as $v) {
                    $string_arr[] = $key.'[]='.$v;
                }
            }else {
                $string_arr[] = $key.'='.$value;
            }
        }
        return implode('&',$string_arr);
    } 

    function getNFTs($owner_address,$filter_contract_addresses = [],$page_key = '',$with_metadata = true) {
        $url = $this->getUrl('/getNFTs');

        $cond = ['owner' =>  $owner_address];
        if (count($filter_contract_addresses) > 0) {
            $cond['contractAddresses'] =  $filter_contract_addresses;
        }

        if (!$with_metadata) {
            $cond['withMetadata'] = false;
        }

        if ($page_key) {
            $cond['pageKey'] =  $page_key;
        }

        Log::debug('请求alchemy的api是：'.$url.'?'.$this->getRequestString($cond));

        $response = Http::get($url.'?'.$this->getRequestString($cond));

        if ($response->successful()) {
            Log::info('请求Alchemy Api成功，条件是:'.json_encode($cond));
            return $response->json();
        }else {
            Log::info('请求Alchemy Api报错,条件是:'.json_encode($cond));
            throw new App\Exceptions\ProgramException("系统错误: 请求NFT API 错误，请稍后再试");
        }
    }

    public function getNFTsByCache($owner_address,$filter_contract_addresses = [],$page_key = '',$with_metadata = true) {

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

    function getNFTMetadata($contract_address,$token_id,$token_type = 'erc721') {
        $url = $this->getUrl('/getNFTMetadata');
        $response = Http::get($url,[
            'contractAddress'   =>  $contract_address,
            'tokenId'           =>  $token_id,
            'tokenType'         =>  $token_type
        ]);
        if ($response->successful()) {
            return $response->json();
        }else {
            Log::info('请求Alchemy Api 获得metadata报错,条件是:'.json_encode([
                'contractAddress'   =>  $contract_address,
                'tokenId'           =>  $token_id,
                'tokenType'         =>  $token_type
            ]));
            throw new App\Exceptions\ProgramException("系统错误: 请求NFT API 错误，请稍后再试");
        }
    }

    function isOwner($owner_address,$contract_address,$token_id) {
        $url = $this->getUrl('/getNFTs');

        $cond = [
            'owner'             =>  $owner_address,
            'contractAddresses' =>  [$contract_address]
        ];

        Log::info('准备请求Alchemy Api 获得isOwner,条件是:'.json_encode($cond));
        Log::info('准备请求Alchemy Api 获得isOwner,url是:'.$url.'?'.$this->getRequestString($cond));

        $response = Http::get($url.'?'.$this->getRequestString($cond));

        if ($response->successful()) {
            $result = $response->json();

            if ($result['totalCount'] == 0) {
                return false;
            }else {
                foreach($result['ownedNfts'] as $nft) {
                    $id = hexdec($nft['id']['tokenId']);
                    if ($id == $token_id) {
                        return true;
                    }
                }
                return false;
            }

        }else {
            Log::info('请求Alchemy Api报错,条件是:'.json_encode($cond));
            throw new App\Exceptions\ProgramException("系统错误: 请求NFT API 错误，请稍后再试");
        }
    }




 }
