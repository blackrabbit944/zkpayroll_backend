<?php

namespace App\Http\Controllers;


use App\Models\ItemHistory;

use App\Services\TxService;
use App\Helpers\Bc;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class CallbackController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ERC721TransferEvent(Request $input)
    {   
        
        $j = json_decode(file_get_contents("php://input"), true);
        Log::info("收到一个 ERC721TransferEvent", [$j]);
        
        if (!$j) {
            return $this->failed("无法解析传入的 JSON");
        }

        if (isset($j['price'])) {
            $j['price'] = bcdiv($j['price'], bcpow(10, 18), 19);
            Bc::trimr0($j['price']);
        }

        $ret = TxService::create($j);

        if ($ret) {
            return $this->success("处理成功");
        } else {
            return $this->failed("回调处理失败", 200);
        }

    }


    
    /**
     * Home
     *
     * @param  Request  $request
     * @return Response
     */
    public function NFTClubMintEvent()
    {
        $j = json_decode(file_get_contents("php://input"), true, 512, JSON_BIGINT_AS_STRING);
        Log::info("收到一个 NFTClubMintEvent", [$j]);

        if (isset($j['price'])) {
            $j['price'] = bcdiv($j['price'], bcpow(10, 18), 19);
            Bc::trimr0($j['price']);
        }

        $ret = TxService::create($j);
        
        if ($ret) {
            return $this->success("处理成功");
        } else {
            return $this->failed("回调处理失败", 200);
        }
        
    }

    public function gstest() {
        return "gstest Success\n" . request()->ip();
    }
}
