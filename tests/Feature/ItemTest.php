<?php

use App\Models\User;

use Illuminate\Support\Facades\DB;

class ItemControllerTest extends TestCase
{

    private function create($contract_address,$token_id) {
        $user = User::factory()->create(['is_super_admin'=>1]);
        $this->signIn($user);

        $data = [
            'contract_address'          =>  $contract_address,
            'token_id'                  =>  $token_id
        ];
        $response = $this->api('post','/item/add',$data);
        return $response;
    }

    /** @test */
    public function f_item_add()
    {
        Event::fake();

        ///1.如果一个非管理员是不允许创建item的
        $user = User::factory()->create(['is_super_admin'=>0]);
        $this->signIn($user);

        $data = [
            'contract_address'          =>  '0xed5af388653567af2f388e6224dc7c4b3241c544',
            'token_id'                  =>  1111
        ];
        $this->apiRequest('post','/item/add',$data)->assertStatus(401);

        ///2.管理员创建一个存在的contract_address
        $user = User::factory()->create(['is_super_admin'=>1]);
        $this->signIn($user);

        $data = [
            'contract_address'          =>  '0xed5af388653567af2f388e6224dc7c4b3241c544',
            'token_id'                  =>  1111
        ];
        $response = $this->api('post','/item/add',$data);
        $this->assertEquals('0xed5af388653567af2f388e6224dc7c4b3241c544',$response->data->contract_address);
        $this->assertEquals(1111,$response->data->token_id);
        $this->assertEquals('https://ikzttp.mypinata.cloud/ipfs/QmYDvPAXtiJg7s8JdRBSLWdgSphQdac8j1YuQNNxcGE1hg/1111.png',$response->data->image_url);

        
        ///3.管理员创建一个不存在的合约地址，会报错
        $data = [
            'contract_address'          =>  '0xed5af388653567af2f388e6224dc7c4b3241c543',
            'token_id'                  =>  1111
        ];
        $response_error = $this->api('post','/item/add',$data);
        $this->assertEquals(400,$response_error->code);
        $this->assertEquals('error',$response_error->status);

    }


    /** @test */
    public function f_item_update() {
        Event::fake();

        $add_response = $this->create('0xed5af388653567af2f388e6224dc7c4b3241c544',1111);

        $user = User::factory()->create(['is_super_admin'=>0]);
        $this->signIn($user);

        $data = [
            'id'            =>  $add_response->data->id,
            'image_url'     =>  'https://ipfs.io/ipfs/QmYDvPAXtiJg7s8JdRBSLWdgSphQdac8j1YuQNNxcGE1hg/1380.png',
        ];
        $response = $this->apiRequest('patch','/item/update',$data)->assertStatus(401);

        $user = User::factory()->create(['is_super_admin'=>1]);
        $this->signIn($user);

        $faker = Faker\Factory::create();
        $data = [
            'id'                    =>  $add_response->data->id,
            'image_url'             =>  'https://ipfs.io/ipfs/QmYDvPAXtiJg7s8JdRBSLWdgSphQdac8j1YuQNNxcGE1hg/1380.png',
        ];
        $response = $this->api('patch','/item/update',$data);
        $this->assertEquals($data['image_url'],$response->data->image_url);

    }

    /** @test */
    public function f_item_load() {
        Event::fake();

        $add_response = $this->create('0xed5af388653567af2f388e6224dc7c4b3241c544',1111);

        $user = User::factory()->create(['is_super_admin'=>0]);
        $this->signIn($user);

        $data = [
            'contract_address'          =>  '0xed5af388653567af2f388e6224dc7c4b3241c544',
            'token_id'                  =>  1111
        ];
        $response2 = $this->api('get','/item/load',$data);
        $this->assertEquals('0xed5af388653567af2f388e6224dc7c4b3241c544',$response2->data->contract_address);
        $this->assertEquals(1111,$response2->data->token_id);

        ///测试一个图片

    }

    /** @test */
    public function f_item_image() {

        $add_response = $this->create('0xed5af388653567af2f388e6224dc7c4b3241c544',1111);

        $user = User::factory()->create(['is_super_admin'=>0]);
        $this->signIn($user);

        $data = [
            'contract_address'          =>  '0xed5af388653567af2f388e6224dc7c4b3241c544',
            'token_id'                  =>  1111,
            'width'                     =>  200
        ];
        $this->apiRequest('get','/item/image',$data)->assertStatus(200)->assertHeader('Content-type', 'image/png');

        // dump($response2);
        ///测试一个图片

    }
}

