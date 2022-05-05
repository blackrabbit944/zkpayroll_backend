<?php

use App\Models\User;

use Illuminate\Support\Facades\DB;

class OrderControllerTest extends TestCase
{

    private function create() {
        $user = User::factory()->create(['is_super_admin'=>0,'wallet_address'=>'0xd45058bf25bbd8f586124c479d384c8c708ce23a']);
        $this->signIn($user);

        $data = [
            'contract_address'          =>  '0xed5af388653567af2f388e6224dc7c4b3241c544',
            'token_id'                  =>  40,
            'price'                     =>  0.25,
            'expire_time'               =>  time() + 70000,
            'sign'                      =>  '123123',
            'wallet_address'            =>  '0xd45058bf25bbd8f586124c479d384c8c708ce23a'
        ];
        $response = $this->api('post','/order/add',$data);
        return $response;
    }

    /** @test */
    public function f_order_add()
    {
        // Event::fake();

        ///1.创建一个订单
        $user = User::factory()->create(['is_super_admin'=>0,'wallet_address'=>'0xd45058bf25bbd8f586124c479d384c8c708ce23a']);
        $this->signIn($user);

        $data = [
            'contract_address'          =>  '0xed5af388653567af2f388e6224dc7c4b3241c544',
            'token_id'                  =>  40,
            'price'                     =>  0.25,
            'expire_time'               =>  time() + 70000,
            'sign'                      =>  '123123',
            'wallet_address'            =>  '0xd45058bf25bbd8f586124c479d384c8c708ce23a'
        ];
        $response = $this->api('post','/order/add',$data);

        $this->assertEquals('0xed5af388653567af2f388e6224dc7c4b3241c544',$response->data->contract_address);
        $this->assertEquals(0.25,$response->data->price);
        $this->assertEquals('0xd45058bf25bbd8f586124c479d384c8c708ce23a',$response->data->from_address);
        $this->assertEquals($data['expire_time'],$response->data->expire_time);

        // 2.创建订单检查用户是否拥有这个item
        // 
        // 
        // 3.创建订单检查签名用户是否是这个用户
        
    }


    /** @test */
    public function f_order_update() {

        Event::fake();

        $add_response = $this->create();

        $data = [
            'contract_address'            =>  '0xed5af388653567af2f388e6224dc7c4b3241c544',
            'token_id'                    =>  40,
            'price'                       =>  0.35,
            'expire_time'                 =>  time() + 100000,
            'wallet_address'              =>  '0xd45058bf25bbd8f586124c479d384c8c708ce23a',
            'sign'                        =>  '123'
        ]; 
        $response = $this->api('patch','/order/update',$data);

        $this->assertEquals('0xed5af388653567af2f388e6224dc7c4b3241c544',$response->data->contract_address);
        $this->assertEquals(0.35,$response->data->price);
        $this->assertEquals($data['expire_time'],$response->data->expire_time);

    }


    /** @test */
    public function f_order_delete() {

        Event::fake();

        $add_response = $this->create();
        $this->seeInDatabase('b_order',[
            'contract_address'            =>  '0xed5af388653567af2f388e6224dc7c4b3241c544',
            'token_id'                    =>  40,
            'delete_time'                 =>  null
        ]);

        $data = [
            'contract_address'            =>  '0xed5af388653567af2f388e6224dc7c4b3241c544',
            'token_id'                    =>  40,
            'wallet_address'              => '0xd45058bf25bbd8f586124c479d384c8c708ce23a',
            'sign'                        =>  '123'
        ]; 
        $response = $this->api('delete','/order/delete',$data);
        $this->assertEquals('success',$response->status);

        $this->notSeeInDatabase('b_order',[
            'contract_address'            =>  '0xed5af388653567af2f388e6224dc7c4b3241c544',
            'token_id'                    =>  40,
            'delete_time'                 =>  0
        ]);

    }
    /** @test */
    public function f_order_load() {
        Event::fake();

        $add_response = $this->create();

        $data = [
            'contract_address'            =>  '0xed5af388653567af2f388e6224dc7c4b3241c544',
            'token_id'                    =>  40,
        ]; 
        $response = $this->api('get','/order/load',$data);

        $this->assertEquals('success',$response->status);
        $this->assertEquals('0xed5af388653567af2f388e6224dc7c4b3241c544',$response->data->contract_address);
        $this->assertEquals(40,$response->data->token_id);


    }

}

