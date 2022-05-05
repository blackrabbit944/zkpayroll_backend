<?php

namespace App\Helpers;

use App\Helpers\Solidity;
// use Ethereum\Ethereum;
use Ethereum\DataType\EthD;
use Ethereum\DataType\EthBytes;
use Ethereum\DataType\EthD20;

use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\RequestException;


class Erc721 {

    protected $contract_address = '';
    protected $contract_interface = null;

    function __construct($contract_address) {
        Log::info('触发了Erc721的构建程序');

        $this->contract_address = $contract_address;
        $this->contract_interface = Solidity::getERC721($contract_address);
    }

    public function getSmartContract() {
        return $this->contract_interface;
    }


    public function ownerOf($token_id = 0) {
        $token_id_formated = new EthD(dechex($token_id));
        $x = $this->getSmartContract()->ownerOf($token_id_formated);
        // dump($x);
        if ($x->val()) {
            return '0x'.$x->val();
        }else {
            return null;
        }
    }

    public function getMetadata() {
        return [
            'symbol'            =>  $this->getSymbol(),
            'name'              =>  $this->getName(),
            'total_supply'      =>  $this->getTotalSupply(),
        ];
    }

    public function getSymbol() {
        return $this->getSmartContract()->symbol()->val();
    }

    public function getBalanceOf($address) {
        $owner_address = new EthD($address);
        $x = $this->getSmartContract()->balanceOf($owner_address);
        return $x->val();
    }

    public function getName() {
        return $this->getSmartContract()->name()->val();
    }


    public function getTotalSupply() {
        $totalSupply = 0;
        try {
            $totalSupply = $this->getSmartContract()->totalSupply()->val();
        }catch (RequestException $e) {
            Log::debug('遇到了访问错误:'.$e->getMessage());
            // echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        return $totalSupply;
    }

    // public function balanceOf($owner_address) {
    //     $owner_address = new EthBytes($owner_address);
    //     $x = $this->getSmartContract()->balanceOf($owner_address);
    //     echo $x->val();
    // }
 }
