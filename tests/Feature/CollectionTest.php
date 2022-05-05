<?php

use App\Models\User;

use Illuminate\Support\Facades\DB;

class CollectionControllerTest extends TestCase
{

    private function create($contract_address) {
        $user = User::factory()->create(['is_super_admin'=>1]);
        $this->signIn($user);

        $data = [
            'contract_address'          =>  $contract_address,
        ];
        $response = $this->api('post','/collection/add',$data);
        return $response;
    }

    /** @test */
    public function f_collection_add()
    {
        ///1.如果一个非管理员是不允许创建collection的
        $user = User::factory()->create(['is_super_admin'=>0]);
        $this->signIn($user);

        $data = [
            'contract_address'          =>  '0xed5af388653567af2f388e6224dc7c4b3241c544',
        ];
        $this->apiRequest('post','/collection/add',$data)->assertStatus(401);

        ///2.管理员创建一个存在的contract_address
        $user = User::factory()->create(['is_super_admin'=>1]);
        $this->signIn($user);

        $data = [
            'contract_address'          =>  '0xed5af388653567af2f388e6224dc7c4b3241c544',
        ];
        $response = $this->api('post','/collection/add',$data);
        $this->assertEquals('0xed5af388653567af2f388e6224dc7c4b3241c544',$response->data->contract_address);
        $this->assertEquals('Azuki',$response->data->name);
        $this->assertEquals('AZUKI',$response->data->symbol);
        $this->assertEquals('ERC721',$response->data->eip_type);

        
        ///3.管理员创建一个不存在的合约地址，会报错
        $data = [
            'contract_address'          =>  '0xed5af388653567af2f388e6224dc7c4b3241c543',
        ];
        $response_error = $this->api('post','/collection/add',$data);
        $this->assertEquals(400,$response_error->code);
        $this->assertEquals('error',$response_error->status);

        ///4.删除一个collection后重新创建则id不会改变
        $data = [
            'contract_address'          =>  '0xed5af388653567af2f388e6224dc7c4b3241c544',
        ];
        $response2 = $this->api('delete','/collection/delete',$data);
        $this->assertEquals('success',$response2->status);

        $data = [
            'contract_address'          =>  '0xed5af388653567af2f388e6224dc7c4b3241c544',
        ];
        $response3 = $this->api('post','/collection/add',$data);
        $this->assertEquals($response->data->id,$response3->data->id);

    }

    /** @test */
    public function f_collection_delete()
    {
        ///1.如果一个非管理员是不允许创建collection的
        $response = $this->create('0xed5af388653567af2f388e6224dc7c4b3241c544');

        ///2.非管理员删除一个现在的collection
        $user = User::factory()->create(['is_super_admin'=>0]);
        $this->signIn($user);

        $data = [
            'contract_address'          =>  '0xed5af388653567af2f388e6224dc7c4b3241c544',
        ];
        $response = $this->apiRequest('delete','/collection/delete',$data)->assertStatus(401);

        
        ///2.管理员删除一个现在的collection
        $user = User::factory()->create(['is_super_admin'=>1]);
        $this->signIn($user);

        $data = [
            'contract_address'          =>  '0xed5af388653567af2f388e6224dc7c4b3241c544',
        ];
        $response = $this->api('delete','/collection/delete',$data);
        $this->assertEquals('success',$response->status);
        

        ///3.管理员删除一个不存在的collection
        $data = [
            'contract_address'          =>  '0xed5af388653567af2f388e6224dc7c4b3241c544',
        ];
        $response = $this->apiRequest('delete','/collection/delete',$data)->assertStatus(400);

    }


    /** @test */
    public function f_collection_update() {

        $add_response = $this->create('0xed5af388653567af2f388e6224dc7c4b3241c544');

        $user = User::factory()->create(['is_super_admin'=>0]);
        $this->signIn($user);

        $data = [
            'discord_link'          =>  'https://discord.com/test',
        ];
        $response = $this->apiRequest('patch','/collection/update',$data)->assertStatus(401);

        $user = User::factory()->create(['is_super_admin'=>1]);
        $this->signIn($user);

        $faker = Faker\Factory::create();
        $img = $this->uploadAvatar($user);
        $data = [
            'id'                    =>  $add_response->data->id,
            'discord_link'          =>  $faker->url,
            'twitter_link'          =>  $faker->url,
            'website_link'          =>  $faker->url,
            'instagram_link'        =>  $faker->url,
            'avatar_img_id'         =>  $img->img_id,
            'item_count'            =>  10000
        ];
        $response = $this->api('patch','/collection/update',$data);

        $this->assertEquals($data['discord_link'],$response->data->discord_link);
        $this->assertEquals($data['twitter_link'],$response->data->twitter_link);
        $this->assertEquals($data['website_link'],$response->data->website_link);
        $this->assertEquals($data['instagram_link'],$response->data->instagram_link);
        $this->assertEquals($data['avatar_img_id'],$response->data->avatar_img_id);
        $this->assertEquals($data['item_count'],$response->data->item_count);

    }


    /** @test */
    public function f_collection_load()
    {
        ///1.如果一个非管理员是不允许创建collection的
        $response = $this->create('0xed5af388653567af2f388e6224dc7c4b3241c544');

        ///2.非管理员删除一个现在的collection
        $user = User::factory()->create(['is_super_admin'=>0]);
        $this->signIn($user);

        $data = [
            'contract_address'          =>  $response->data->contract_address,
        ];
        $response2 = $this->api('get','/collection/load',$data);
        $this->assertEquals('Azuki',$response2->data->name);
        $this->assertEquals('AZUKI',$response2->data->symbol);
        $this->assertEquals('0xed5af388653567af2f388e6224dc7c4b3241c544',$response2->data->contract_address);


    }  
}

