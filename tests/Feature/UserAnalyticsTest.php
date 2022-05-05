<?php

use App\Models\User;
use App\Models\Order;
use App\Models\UserAnalytics;

use Illuminate\Support\Facades\DB;
use App\Services\UserService;
use App\Models\ItemHistory;

class UserAnalyticsControllerTest extends TestCase
{



    /** @test */
    public function f_user_analytics_whitelist() {

        ///测试逻辑
        ///1.B用户是从A用户邀请来的,则B注册以后A会增加一个invite_count
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        UserService::setInviteAddress($user2,$user->wallet_address);
        $this->seeInDatabase('b_user_analytics',[
            'wallet_address'    =>  $user->wallet_address,
            'invite_count'      =>  1,
            'is_wl'             =>  0
        ]);

        ///2.C用户也是A用户邀请来的，则C注册完成以后A可以获得is_wl=1
        $user3 = User::factory()->create();

        UserService::setInviteAddress($user3,$user->wallet_address);
        $this->seeInDatabase('b_user_analytics',[
            'wallet_address'    =>  $user->wallet_address,
            'invite_count'      =>  2,
            'is_wl'             =>  1
        ]);


        ///3.A用户卖出NFT以后，sell_volumne要增加一个sell_volume
        ///4.A用户购买了NFT以后，buy_volumne要增加一个buy_volumne
        $order = Order::factory()->create(['from_address'=>$user->wallet_address]);
        $item_history = ItemHistory::factory()->create([
            'contract_address'=> $order->contract_address,
            'token_id'        => $order->token_id,
            'action_name'     => 'sale',
            'from_address'    => $user->wallet_address,
            'to_address'      => $user2->wallet_address,
            'action_time'     => time(),
            'price'           => $order->price,
        ]);

        $row = UserAnalytics::where(['wallet_address'    =>  $user->wallet_address])->first();
        $this->assertEquals($order->price,$row->sell_volume);

        $row2 = UserAnalytics::where(['wallet_address'    =>  $user2->wallet_address])->first();
        $this->assertEquals($order->price,$row2->buy_volume);

        ///5.A用户卖出了超过0.3ETH的NFT，则A用户会增加一个is_blackgold_nft=1
        
        $order2 = Order::factory()->create(['from_address'=>$user->wallet_address,'price'=>0.3]);
        $item_history = ItemHistory::factory()->create([
            'contract_address'=> $order2->contract_address,
            'token_id'        => $order2->token_id,
            'action_name'     => 'sale',
            'from_address'    => $user->wallet_address,
            'to_address'      => $user2->wallet_address,
            'action_time'     => time(),
            'price'           => $order2->price,
        ]);
        $row = UserAnalytics::where(['wallet_address'=>$user->wallet_address])->first();
        $this->assertEquals('1',$row->is_blackgold_wl);
        $this->assertEquals('1.290000',$row->sell_volume);


        $row2 = UserAnalytics::where(['wallet_address'    =>  $user2->wallet_address])->first();
        $this->assertEquals('1.290000',$row2->buy_volume);
        $this->assertEquals('0',$row2->is_blackgold_wl);



    }

    /** @test */
    public function f_user_analytics_item_history_create_event() {
        

        $user = User::factory()->create();
        $user2 = User::factory()->create();

        $order = Order::factory()->create(['from_address'=>$user->wallet_address]);
        $item_history = ItemHistory::factory()->create([
            'contract_address'=> $order->contract_address,
            'token_id'        => $order->token_id,
            'action_name'     => 'sale',
            'from_address'    => $user->wallet_address,
            'to_address'      => $user2->wallet_address,
            'action_time'     => time(),
            'price'           => $order->price,
        ]);


        //2.这个用户要在user_analytics表被标记order_create是1
        $this->seeInDatabase('b_user_analytics',[
            'wallet_address'    =>  $user->wallet_address,
            'has_sell_record'   =>  1,
        ]);

        $this->seeInDatabase('b_user_analytics',[
            'wallet_address'    =>  $user2->wallet_address,
            'has_buy_record'   =>  1,
        ]);


    }
}

