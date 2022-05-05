<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Exceptions\ProgramException;
class Moralis {

    public $api_key = '';

    function __construct() {
        $this->api_key = env('MORALIS_API_KEY');
    }

    private function _getBaseUrl() {
        return 'https://deep-index.moralis.io/api/v2/';
    }

    private function getUrl($url) {
        return $this->_getBaseUrl() . $url;
    }

    // private function getRequestString($cond) {
    //     $string_arr = [];
    //     foreach($cond as $key => $value) {
    //         if (is_array($value)) {
    //             foreach($value as $v) {
    //                 $string_arr[] = $key.'[]='.$v;
    //             }
    //         }else {
    //             $string_arr[] = $key.'='.$value;
    //         }
    //     }
    //     return implode('&',$string_arr);
    // } 

    function getNftOwner($contract_address,$token_id) {
        $url = $this->getUrl('/nft/'.$contract_address.'/'.$token_id.'/owners');

        $response = Http::withHeaders([
            'X-API-KEY' => $this->api_key
        ])->get($url,[
            'chain' =>  'eth'
        ]);

        if ($response->successful()) {
            return $response->json();
        }else {
            Log::info('请求Moralis getNftOwner Api报错,条件是:'.json_encode($cond));
        }

    }

    function getNFTMetadata($contract_address) {
        $url = $this->getUrl('/nft/'.$contract_address.'/metadata');

        $response = Http::withHeaders([
            'X-API-KEY' => $this->api_key
        ])->get($url,[
            'chain' =>  'eth'
        ]);

        if ($response->successful()) {
            return $response->json();
        }else {
            $result = $response->json();
            Log::info('请求Moralis getNFTMetadata Api报错,报错是:'.$result['message']);
            throw new ProgramException($result['message']);
        }
    }

 }
