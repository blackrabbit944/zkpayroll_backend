<?php

namespace App\Services;

use App\Models\Collection;
use App\Helpers\Moralis;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Exceptions\ProgramException;
use App\Exceptions\ApiException;

use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Models\Item;
use App\Models\Order;

use App\Helpers\Erc721;


class CollectionService 
{
    static public function create($data = []) {

        $basic = [
            'contract_address'  =>  '',
            'chain'             =>  'eth',
            'eip_type'          =>  'erc721'
        ];
        $merged = array_merge($basic,$data);

        if ($merged['chain'] !== 'eth') {
            throw new ProgramException('暂时不支持非ETH网络的NFT交易');
            return;
        }

        if ($merged['eip_type'] !== 'erc721') {
            throw new ProgramException('暂时不支持非Erc721的NFT交易');
            return;
        }

        ///从API获得NFT数据并存入数据库
        $collection = Collection::where([
            'contract_address' => $merged['contract_address'],
        ])->first();

        if ($collection) {
            return $collection;
        }else {

            ///先检查在NFT表中是否已经存在这个NFT的信息了
            Log::debug('准备从Erc721链上数据获得NFT的collection的Metadata，合约地址是：'.$merged['contract_address']);

            // $moralisHelper = new Moralis();
            // $metadata = $moralisHelper->getNFTMetadata($merged['contract_address']);

            $erc721Helper = new Erc721($merged['contract_address']);
            $metadata = $erc721Helper->getMetadata();

            $save_data = [
                'contract_address'  =>  $merged['contract_address'],
                'name'              =>  $metadata['name'],
                'symbol'            =>  $metadata['symbol'],
                'eip_type'          =>  'erc721',
                'item_count'        =>  $metadata['total_supply']
            ];
            
            Log::debug('获得NFT整理后的结果是:'.json_encode($save_data));

            return Collection::create($save_data);
        }



    }




}
