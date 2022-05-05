<?php

use App\Models\User;
use App\Models\Order;
use App\Models\ItemHistory;

use App\Helpers\Erc721;
use App\Services\ItemHistoryService;

class Erc721Test extends TestCase
{


    /** @test */
    public function u_get_metadata()
    {

        $contract_address = '0x25ed58c027921e14d86380ea2646e3a1b5c55a8b';
        $Erc721 = new Erc721($contract_address);


        // $d = $Erc721->ownerOf(1);
        $d = $Erc721->getMetadata();
        dump($d);
    }   


}   