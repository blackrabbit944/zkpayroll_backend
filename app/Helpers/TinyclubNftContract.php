<?php

namespace App\Helpers;

use App\Helpers\Solidity;
use Ethereum\DataType\EthD;
use Ethereum\DataType\EthBytes;
use Ethereum\DataType\EthD20;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Cache\LockTimeoutException;

use App\Helpers\Erc721;

class TinyclubNftContract extends Erc721 {

    protected $contract_address = '';
    protected $contract_interface = null;

    function __construct($contract_address) {
        if (!$contract_address) {
            Log::error('TINYCLUB_CONTRACT合约为空，请检查');
            exit;
        }
        $this->contract_address = $contract_address;
        $this->contract_interface = Solidity::getTinyclubNft($contract_address);
    }

    public function getSmartContract() {
        return $this->contract_interface;
    }

    public function getMintDataPart1ByCache($cache_time) {

        $arr = [
            'contract_address' => $this->contract_address,
        ];
        $ckey = sprintf("mintdata1_%s", md5(json_encode($arr)));

        $mint_data1 = Cache::get($ckey);

        ///如果数据存在，但是数据创建时间大于了缓存时间
        if ($mint_data1) {

            if (time() - $mint_data1['create_time'] >= $cache_time) {
                ///进入重建缓存阶段
                $lock = Cache::lock($ckey.'_lock',60);

                ///如果没有重建的锁，则发起重建，否则返回老数据
                if($lock->get()){
                    ///重建缓存
                    try {
                        $mint_data1 = $this->rebuildMintDataPart1Cache($ckey,$cache_time);
                    }catch (Exception $e) {
                        Log::debug('重建缓存出错:'.$e->message());
                    }finally {
                        $lock->release();
                    }

                }
            }
            
        }else {
            $mint_data1 = $this->rebuildMintDataPart1Cache($ckey,$cache_time);
        }
        return $mint_data1;

    }

    public function getMintDataPart2ByCache($cache_time) {

        $arr = [
            'contract_address' => $this->contract_address,
        ];
        $ckey = sprintf("mintdata2_%s", md5(json_encode($arr)));

        $mint_data2 = Cache::get($ckey);


        ///如果数据存在，但是数据创建时间大于了缓存时间
        if ($mint_data2) {
            
            if (time() - $mint_data2['create_time'] >= $cache_time) {

                Log::info('进入mint_data2的缓存重建阶段');
                ///进入重建缓存阶段
                $lock = Cache::lock($ckey.'_lock',60);

                ///如果没有重建的锁，则发起重建，否则返回老数据
                if($lock->get()){

                    try {
                        $mint_data2 = $this->rebuildMintDataPart2Cache($ckey,$cache_time);
                    }catch (Exception $e) {
                        Log::debug('重建缓存出错:'.$e->message());
                    }finally {
                        $lock->release();
                    }

                    $lock->release();
                }
            }
            
        }else {
            $mint_data2 = $this->rebuildMintDataPart2Cache($ckey,$cache_time);

        }
        return $mint_data2;

    }

    private function rebuildMintDataPart1Cache($key,$cache_time) {

        $cache_time_add_rebuild_time = $cache_time + 600;   ///增加600秒作为缓存创建时间,因为缓存会在到期后重建，所以这个时间需要比到期后时间大即可，而且是需要大一个比较多的数字

        $mint_data1 = [
            'mint_price'        =>  $this->getMintPrice(),
            'max_supply'        =>  $this->getMaxSupply(),
            'create_time'       =>  time()
        ];

        Cache::put($key, $mint_data1, $cache_time_add_rebuild_time);

        return $mint_data1;
    }

    private function rebuildMintDataPart2Cache($key,$cache_time) {

        Log::info('进入mint_data2的缓存重建阶段:'.$cache_time);

        $cache_time_add_rebuild_time = $cache_time + 600;   ///增加600秒作为缓存创建时间,因为缓存会在到期后重建，所以这个时间需要比到期后时间大即可，而且是需要大一个比较多的数字

        $mint_data2 = [
            'total_minted'      =>  $this->getTotalMinted(),
            'create_time'       =>  time()
        ];

        Log::info('进入mint_data2的缓存重建阶段结束，拿到的数据是:'.json_encode($mint_data2));

        Cache::put($key, $mint_data2, $cache_time_add_rebuild_time);

        return $mint_data2;
    }

    /*
    *   为了不至于耗尽系统资源，因此做了mint_data数据的缓存。
    *   1.缓存对不常变化的量缓存10分钟，对可能经常变化的数据缓存10秒。
    *   2.采用一个锁机制，保证缓存不会批量的重建，这样的话也会对系统资源进行冲击，比如1000个人同时在请求时候，如果缓存过期则只会有一个请求重建缓存
    *   3.缓存时间要做成可以配置的。
    */
    public function getMintDataByCache() {

        $fast_cache_time = 10;
        $slow_cache_time = 600;

        $contract_address = $this->contract_address;

        $arr = [
            'contract_address' => $this->contract_address,
        ];
        $ckey1 = sprintf("mintdata1_%s", md5(json_encode($arr)));

        $mint_data1 = $this->getMintDataPart1ByCache($slow_cache_time);
        $mint_data2 = $this->getMintDataPart2ByCache($fast_cache_time);

        return array_merge($mint_data1,$mint_data2);
    }

    public function getMintData() {
        // Log::debug('获得一个不带缓存的版本');
        $data  = [ 
            'mint_price'        =>  $this->getMintPrice(),
            'total_minted'      =>  $this->getTotalMinted(),
            'max_supply'        =>  $this->getMaxSupply(),
        ];
        return $data;
    }

    public function getMintPrice() {

        $sale_price = 0;
        try {
            $sale_price = $this->getSmartContract()->_salePrice()->val();
            $sale_price = bcdiv($sale_price,pow(10,18),8);

            return $sale_price;
        }catch (RequestException $e) {
            Log::debug('遇到了访问错误:'.$e->getMessage());
            // echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        return $sale_price;
    }

    public function getMaxSupply() {
        return $this->getSmartContract()->_maxSupply()->val();
    }
    public function getTotalMinted() {
        return $this->getSmartContract()->totalMinted()->val();
    }
 }
