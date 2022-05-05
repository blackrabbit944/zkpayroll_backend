<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

use App\Helpers\Arr;
/**
 * 
 */
class Opensea  {

    protected $base_url = 'https://api.opensea.io/api';
    protected $version = 'v1';

    public function getAsset($data) {
        
        $url = $this->base_url . '/' . $this->version .'/asset/' . $data['asset_contract_address'] . '/' . $data['token_id'];

        Log::info('准备请求Opensea Api,url:'.$url);
        // Log::info('准备请求Opensea Api,account_address:'.$data['account_address']);

        $response = Http::get($url, [
        ]);

        Log::info('请求结果:'.$response);
        $ret = json_decode($response,true);

        return $ret;
    }

    public function isAssetBelongAddressByData($result,$wallet_address) {

        if (!isset($result['owner'])) {
            Log::debug('没有在result中找到owner字段，无法判断是否是这个地址所有');
            return null;
        }

        if ($result['owner']['address'] == $wallet_address) {
            Log::debug('owner地址是'.$result['owner']['address'].',和传入检查的'.$wallet_address.'一致');
            return true;
        }

        return false;
    }

    public function isAssetBelongAddress($data,$wallet_address) {
        $result = $this->getAsset($data);
        return $this->isAssetBelongAddressByData($result,$wallet_address);
    }
}
