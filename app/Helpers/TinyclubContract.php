<?php

namespace App\Helpers;

use App\Helpers\Solidity;
// use Ethereum\Ethereum;
use Ethereum\DataType\EthD;
use Ethereum\DataType\EthBytes;
use Ethereum\DataType\EthD20;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use GuzzleHttp\Exception\RequestException;

class TinyclubContract {

    protected $contract_address = '';
    protected $contract_interface = null;

    function __construct() {
        $this->contract_address = env('TINYCLUB_CONTRACT');
        if (!$this->contract_address) {
            Log::error('TINYCLUB_CONTRACT合约为空，请检查');
        }
        $this->contract_interface = Solidity::getTinyclubParent($this->contract_address);
    }

    public function getSmartContract() {
        return $this->contract_interface;
    }


    public function _platformFeePPM() {
        $x = $this->getSmartContract()->platformFeePPM();
        $ppm = $x->val();
        $ppm_pre = bcdiv($ppm,1000000,4);
        return $ppm_pre;
    }

    public function platformFeePPM($use_cache = true) {
        
        $cache_time = 86400;    ///每天缓存一次
        $ckey = "tinyclub_platform_fee_ppm";

        $ppm_pre = Cache::remember($ckey,$cache_time,function() {
            Log::Debug('debug,没有命中缓存:tinyclub_platform_fee_ppm');
            return $this->_platformFeePPM();
        });

        Log::Debug('debug,最后获得的tinyclub_platform_fee_ppm是:'.$ppm_pre);

        return $ppm_pre;
    }


 }
