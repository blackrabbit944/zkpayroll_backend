<?php
namespace App\Helpers;

use App\Models\Verify;
use Illuminate\Support\Facades\Log;

/**
 * 
 */
class Role
{
    ///检查用户是否是club的用户
    static function isContractManager($contract_address,$user_id = null) {

        if (!auth('api')->user() && !$user_id) {
            Log::info('检查是否是否可以管理'.$contract_address.'时候，传入的用户是空');
            return false;
        }

        if (!$user_id) {
            $user_id = auth('api')->user()->user_id;
        }


        $verify = Verify::where([
            'user_id'           =>  $user_id,
            'contract_address'  =>  $contract_address,
            'is_verified'       =>  1
        ])->first();

        if (!$verify) {
            return false;
        }
        
        return true;
    }
}