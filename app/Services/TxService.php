<?php

namespace App\Services;

use App\Models\Tx;


use Illuminate\Support\Facades\Log;
use App\Exceptions\ProgramException;
use App\Exceptions\ApiException;

// use Carbon\Carbon;
// use Illuminate\Http\Request;

use App\Models\Traits\ErrorMessage;


class TxService 
{
    use ErrorMessage;

    static public function create($data = []) {

        Log::debug('创建Tx被触发');
        //dump($data);

        $tx = Tx::where([
            'tx_hash'       =>  $data['tx_hash'],
            'tx_log_id'   =>  (isset($data['tx_log_id'])) ?  $data['tx_log_id'] : 0,
        ])->first();

        if ($tx) {
            return $tx;
        }

        ///从Collection先创建一个Collectoon
        $tx = Tx::create($data);

        return $tx;
    }
 
}
