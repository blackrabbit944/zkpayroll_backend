<?php

use App\Models\User;
use App\Models\Collection;

use App\Services\CollectionService;

class CollectionTest extends TestCase
{
    

    /** @test */
    public function u_create_collection()
    {

        $contract_address = '0xed5af388653567af2f388e6224dc7c4b3241c544';

        $collectionService = new CollectionService();
        $coll = $collectionService->create([
           'contract_address'   =>   $contract_address
        ]);

        $this->assertEquals('0xed5af388653567af2f388e6224dc7c4b3241c544',$coll->contract_address);
        $this->assertEquals('Azuki',$coll->name);
        $this->assertEquals('AZUKI',$coll->symbol);
        $this->assertEquals('ERC721',$coll->eip_type);
        
    }   



}