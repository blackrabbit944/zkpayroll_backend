<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Order;

use App\Helpers\Moralis;
use App\Helpers\Alchemy;

use App\Events\CreateItemEvent;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

use App\Exceptions\ProgramException;
use App\Exceptions\ApiException;

use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Helpers\Image as ImageHelper;

class OrderService 
{

    static public function cancelOrder($cond,$action_time = 0) {

        Log::debug('根据条件撤销order，条件是:'.json_encode($cond), ['action_time' => $action_time]);
        $query = Order::where($cond);

        //订单系统不再记录成交订单，所以不存在这部分处理了
        if ($action_time > 0) {
            $query = $query->where('create_time' , '<' , $action_time);
        }

        $orders = $query->get();
        Log::debug('根据条件撤销order，条件查询到的数据条数是:'.$orders->count());

        foreach ($orders as $order) {
            $order->delete();
        }

        return true;
    }

    static public function create($data = []) {


        $order = Order::where([
            'contract_address'       => $data['contract_address'],
            'token_id'               => $data['token_id'],
        ])->first();

        if ($order) {
            return $order;
        }

        $hash = md5(json_encode([
            'contract_address'       => $data['contract_address'],
            'token_id'               => $data['token_id'],
        ]));
        $lock_name = sprintf('create_order_%s',$hash);

        Order::mustLock($lock_name);

        do {

            $ret = false;

            ///从Collection先创建一个Collectoon
            $item = ItemService::create([
                'contract_address'  =>  $data['contract_address'],
                'token_id'          =>  $data['token_id'],
            ]);

            if (!$item) {
                Log::debug('创建Item失败：'.json_encode([
                    'contract_address'  =>  $data['contract_address'],
                    'token_id'          =>  $data['token_id'],
                ]));
                break;
            }

            Log::debug('准备创建订单');
            $save_data = [
                'token_id'          =>  $data['token_id'],
                'contract_address'  =>  $data['contract_address'],
                'price'             =>  $data['price'],
                'expire_time'       =>  $data['expire_time'],
                'from_address'      =>  $data['wallet_address']
            ];
            Log::debug('创建订单数据:'.json_encode($save_data));

            $order = Order::create($save_data);

            if ($order) {
                $ret = true;
            }

        }while (0);

        Order::unlock($lock_name);
        
        if ($ret != true) {
            DB::rollback();
        } else {
            DB::commit();
        }

        return $order;

     }


 
}
