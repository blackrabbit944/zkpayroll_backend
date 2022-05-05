<?php

namespace App\Observers;

use Illuminate\Support\Facades\Log;

use App\Services\ItemHistoryService;
use App\Models\ItemHistory;

use App\Services\OrderService;
use App\Models\Order;
use App\Models\Collection;

use App\Events\CreateItemHistoryEvent;

class ItemHistoryObserver
{

    public function creating(ItemHistory $item_history)
    {
        Log::info('ItemHistory Observer 监听到creating事件');
    }

    /**
     * Handle the post "created" event.
     *
     * @return void
     */
    public function created(ItemHistory $item_history)
    {
        Log::info('ItemHistory Observer 监听到创建了条目:'.json_encode($item_history->toArray()));
        switch($item_history->action_name) {
            case 'transfer':

                ///0. 更新item所属的地址
                if ($item_history->item) {
                    $item_history->item->fill([
                        'from_address'  =>  $item_history->to_address
                    ])->save();
                }

                // 1. 关闭相关的订单
                $condClose = [
                    'contract_address'     =>  $item_history->contract_address,
                    'token_id'             =>  $item_history->token_id,
                    'from_address'         =>  $item_history->from_address,
                ];
                
                Log::debug('关闭对应订单'.json_encode([
                    'contract_address'     =>  $item_history->contract_address,
                    'token_id'             =>  $item_history->token_id,
                    'from_address'         =>  $item_history->from_address,
                ]));

                OrderService::cancelOrder($condClose, $item_history->action_time);

                break;

            case 'sale':
                $condSale = [
                    'contract_address'     =>  $item_history->contract_address,
                    'token_id'             =>  $item_history->token_id,
                    'from_address'         =>  $item_history->from_address,
                ];

                /// 成交的价格，单位：ether
                $price = $item_history->price;


                // 2. 更新一下 b_collection 中的累计成交量
                $cond = [
                    'contract_address' => getenv('CLOSESKY_CONTRACT_ADDRESS'),
                ];
                Collection::where($cond)->increment('accumulate_amount', $price);
                Log::info("更新了 b_collection 中的累计成交量", [$cond, $price]);



                // 3. 将成交信息写入 order 表
                // $data = [
                //     'deal_price' => $price,
                //     'finish_time' => $item_history->action_time,
                //     'deal_address' => $item_history->to_address,
                    
                // ];
                // Order::where($condSale)->update($data);

                // Log::info("将成交信息写入了 order 表", [$condSale, $data]);

                break;
        }


        // 3.记录用户的行为(这里是异步触发的部分)
        event(new CreateItemHistoryEvent($item_history)); 
        
    }

    /**
     * Handle the post "updated" event.
     *
     * @param  \App\Post  $order
     * @return void
     */
    public function updated(ItemHistory $item_history)
    {
        //
    }

    /**
     * Handle the post "deleted" event.
     *
     * @param  \App\Models\ItemHistory  $order
     * @return void
     */
    public function deleted(ItemHistory $item_history)
    {

    }

    /**
     * Handle the post "restored" event.
     *
     * @param  \App\Post  $order
     * @return void
     */
    public function restored(ItemHistory $item_history)
    {
        //
    }

    /**
     * Handle the post "force deleted" event.
     *
     * @param  \App\Post  $order
     * @return void
     */
    public function forceDeleted(ItemHistory $order)
    {
        //
    }



}
