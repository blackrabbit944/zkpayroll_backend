<?php

namespace App\Observers;

use Illuminate\Support\Facades\Log;

use App\Events\CreateOrderEvent;
use App\Models\Order;
use App\Models\Item;

class OrderObserver
{

    public function creating(Order $order)
    {
        $order->contract_address = strtolower($order->contract_address);
        $order->from_address = strtolower($order->from_address);

        return $order;
    }

    /**
     * Handle the post "created" event.
     *
     * @return void
     */
    public function created(Order $order)
    {
        Log::debug('Order Observer 监听到创建order创建事件');
        // dump($order->item);

        $order->item->fill([
            'price'         =>  $order->price,
            'expire_time'   =>  $order->expire_time
        ])->save();


        ///触发一次订单创建的事件
        event(new CreateOrderEvent($order));
        
        Log::debug('Order Observer 更新了item对应的价格');
    }

    /**
     * Handle the post "updated" event.
     *
     * @param  \App\Post  $order
     * @return void
     */
    public function updated(Order $order)
    {
        $order->item->fill([
            'price' =>  $order->price,
            'expire_time'   =>  $order->expire_time
        ])->save();
        Log::debug('Order Observer 更新了item对应的价格');
    }

    /**
     * Handle the post "deleted" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function deleted(Order $order)
    {
        $order->item->fill([
            'price'         =>  null,
            'expire_time'   =>  null
        ])->save();
    }

    /**
     * Handle the post "restored" event.
     *
     * @param  \App\Post  $order
     * @return void
     */
    public function restored(Order $order)
    {
        //
    }

    /**
     * Handle the post "force deleted" event.
     *
     * @param  \App\Post  $order
     * @return void
     */
    public function forceDeleted(Order $order)
    {
        //
    }



}
