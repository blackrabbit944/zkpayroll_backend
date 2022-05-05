<?php

use App\Models\User;
use App\Models\Order;
use App\Models\ItemHistory;

use App\Services\OrderService;
use App\Services\ItemHistoryService;

class ItemHistoryItem extends TestCase
{
    private function createOrder() {

        $user = User::where(['wallet_address'=>'0xd45058bf25bbd8f586124c479d384c8c708ce23a'])->first();
        if (!$user) {
            $user = User::factory()->create(['is_super_admin'=>0,'wallet_address'=>'0xd45058bf25bbd8f586124c479d384c8c708ce23a']);
        }

        $this->signIn($user);

        $order = Order::factory()->create([
            'price'                     =>  0.25,
            'expire_time'               =>  time() + 70000,
            'from_address'              =>  '0xd45058bf25bbd8f586124c479d384c8c708ce23a'
        ]);

        return $order;
    }

    /** @test */
    public function u_create_item_history()
    {


        // Event::fake();
        ///1.情况1:
        ///  - item 拥有一个挂单
        ///  - item_history 发现了item被转移
        ///  - item 的挂单需要立即被取消
        $order = $this->createOrder();

        ItemHistoryService::create([
            'contract_address'          =>  $order->contract_address,
            'token_id'                  =>  $order->token_id,
            'action_name'               =>  'transfer',
            'from_address'              =>  '0xd45058bf25bbd8f586124c479d384c8c708ce23a',
            'to_address'                =>  '0xd45058bf25bbd8f586124c479d384c8c708ce23b',
            'action_time'               =>  time()+1
        ]);


        $this->notSeeInDatabase('b_order',[
            'id'            =>  $order->id,
            'delete_time'   =>  null
        ]);

        return;

        ///2.情况2:
        ///  - item 拥有一个挂单
        ///  - 监听到一个用户针对一个NFT移除了所有的授权
        ///  - item 的挂单立即被取消
        $order = $this->createOrder();
        OrderService::cancelOrder([
            'contract_address'          =>  $order->contract_address,
            'token_id'                  =>  $order->token_id,
            'from_address'              =>  '0xd45058bf25bbd8f586124c479d384c8c708ce23a',
        ],time());
        $this->notSeeInDatabase('b_order',[
            'id'            =>  $order->id,
            'delete_time'   =>  null
        ]);
        
        ///3.情况3
        ///  - item 拥有一个挂单
        ///  - 监听到一个用户针对一个NFT移除了所有的授权，别人转给作者，我们很久以后才发现
        ///  - item 的挂单不会被取消
        $order = $this->createOrder();

        ItemHistoryService::create([
            'contract_address'          =>  $order->contract_address,
            'token_id'                  =>  $order->token_id,
            'action_name'               =>  'transfer',
            'from_address'              =>  '0xd45058bf25bbd8f586124c479d384c8c708ce23b',
            'to_address'                =>  '0xd45058bf25bbd8f586124c479d384c8c708ce23a',
            'action_time'               =>  time()+1
        ]);

        $this->seeInDatabase('b_order',[
            'id'            =>  $order->id,
            'delete_time'   =>  null
        ]);
    }   


}   