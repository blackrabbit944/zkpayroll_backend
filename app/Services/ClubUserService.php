<?php

namespace App\Services;

use App\Models\ClubUser;
// use App\Helpers\Erc721;

use Illuminate\Support\Facades\Log;

// use App\Exceptions\ProgramException;
// use App\Exceptions\ApiException;
// use Carbon\Carbon;

class ClubUserService 
{

    static public function get($contract_address,$address) {

        $user = ClubUser::where([
            'contract_address'  =>  $contract_address,
            'wallet_address'    =>  $address
        ])->first();

        if (!$user) {
            $user = ClubUser::create([
                'contract_address'  =>  $contract_address,
                'wallet_address'    =>  $address,
                'join_time'         =>  0,
                'left_time'         =>  0,
                'token_number'      =>  0
            ]);
        }

        return $user;
    }




}
