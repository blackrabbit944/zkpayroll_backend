<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Club;
use App\Http\Requests\NftRequest;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use App\Helpers\NftImage;

class MiscController extends Controller
{
    public function Erc721Metadata(Request $r) {
        $contract_address = $r->route('contract_address');
        $token_id = $r->route('token_id');
        
        $contract_address = strtolower($contract_address);

        $club = Club::where(['contract_address' => $contract_address])->first();
        if (!$club){ 
            return $this->failed("No such club of contract {$contract_address}");
        }

        $image_url = sprintf("%s/v1/nftassets/image?name=%s&type=%s&unique_name=%s&font=%s",
                        env('APP_URL'),           
                        rawurlencode($club['name_in_nft']),
                        rawurlencode($club['nft_bg']),
                        rawurlencode($club['unique_name']),
                        rawurlencode($club['nft_font'])
    );

        $meta = [
            'id' => $token_id,
            'name' => $club['name_in_nft'],
            'description' => $club['introdution'],
            'external_url' => env('SITE_URL') . "/$contract_address",
            'image' => $image_url,
            'attributes' => [],
        ];
        return $meta; 
    }
}
