<?php

use App\Models\User;
use App\Models\Profile;

use Illuminate\Support\Str;
use App\Events\CreateItemEvent;

class ProfileControllerTest extends TestCase
{

    /** @test */
    public function f_profile_add()
    {

        ///测试添加profile

        ///1.新建一个用户
        $faker = Faker\Factory::create();

        $user = User::factory()->create();
        $this->signIn($user);

        ///2.为这个用户新建一个profile

        $img = $this->uploadImage($user);

        $data = [
            'name'  =>  $faker->name,
            'bio'   =>  $faker->sentence(),
            'ens'   =>  $faker->word.'.eth',
            'unique_name'   =>  'new1',
            'avatar_img_id' =>  $img->img_id,
        ];
        $response = $this->api('patch','/profile/set',$data);

        ///3.断言这个profile是存在的，并且内容如同我们创建时一样
        $this->seeInDatabase('b_profile', [
            'user_id'   => $user->user_id,
            'name'      => $data['name'],
            'bio'       => $data['bio'],
            'ens'       => $data['ens'],
            'avatar_img_id'    => $data['avatar_img_id']
        ]);

        ////3.再次创建时候要检查unique_name不能重复
        $user2 = User::factory()->create();
        $this->signIn($user2);
        $img = $this->uploadImage($user2);

        $data = [
            'name'  =>  $faker->name,
            'bio'   =>  $faker->sentence(),
            'ens'   =>  $faker->word.'.eth',
            'unique_name'   =>  'new1',
            'avatar_img_id' =>  $img->img_id,
        ];
        $response = $this->apiRequest('patch','/profile/set',$data)->assertStatus(400);
    }

    /** @test */
    public function f_profile_update()
    {

        ///测试更新profile
        ///1.新建一个用户和它对应的profile
        $faker = Faker\Factory::create();

        $user = User::factory()->create();
        $this->signIn($user);


        $profile = Profile::factory()->create([
            'user_id'   =>  $user->user_id,
        ]);


        $data = [
            'name'  =>  $faker->name,
            'bio'   =>  $faker->sentence(),
        ];
        $response = $this->api('patch','/profile/set',$data);


        ///2.修改这个profile，断言内容如同修改后一样
        $this->seeInDatabase('b_profile', [
            'user_id'   => $user->user_id,
            'name'      => $data['name'],
            'bio'       => $data['bio'],
        ]);

    }

    /** @test */
    public function f_profile_load()
    {


        ///测试更新profile
        ///1.新建一个用户和它对应的profile
        $faker = Faker\Factory::create();

        $user = User::factory()->create();
        $this->signIn($user);

        ///2.为这个用户新建一个profile
        $data = [
            'name'  =>  $faker->name,
            'bio'   =>  $faker->sentence(),
        ];
        $response = $this->api('patch','/profile/set',$data);
        $this->seeInDatabase('b_profile', [
            'user_id'   => $user->user_id,
            'name'      => $data['name'],
            'bio'       => $data['bio'],
        ]);


        ///3.通过user_load接口能获得对应的profile
        $response = $this->api('get','/profile/load',['user_id'=>$user->user_id]);

        $this->assertEquals($data['name'],$response->data->profile->name);
        $this->assertEquals($data['bio'],$response->data->profile->bio);

    }


    /** @test */
    public function f_profile_nft_avatar_set()
    {
        Event::fake();

        ///用户正常添加一个NFT头像，成功
        $user = User::factory()->create(['wallet_address'=>'0x19f43E8B016a2d38B483aE9be67aF924740ab893']);
        $this->signIn($user);

        //first row
        $response = $this->api('patch','/profile/set',[
            'nft_contract_address'   =>  '0x5a0d4479aed030305a36a1fb516346d533e794fb',
            'nft_token_id'           =>  '6711',
        ]);

        $this->assertEquals('success',$response->status);
        $this->assertEquals('0x5a0d4479aed030305a36a1fb516346d533e794fb',$response->data->user->profile->show_avatar->contract->contract_address);
        $this->assertEquals('6711',$response->data->user->profile->show_avatar->contract->token_id);

        ///确认下载头像的事件被调用
        Event::assertDispatched(CreateItemEvent::class);

        ///用户添加一个不属于自己的NFT头像，失败
        $user2 = User::factory()->create(['wallet_address'=>'0x19f43E8B016a2d38B483aE9be67aF924740ab894']);
        $this->signIn($user2);

        $response = $this->api('patch','/profile/set',[
            'nft_asset_token_address'   =>  '0x5a0d4479aed030305a36a1fb516346d533e794fb',
            'nft_token_id'              =>  '5600',
        ]);

        $this->assertEquals('success',$response->status);
        $this->assertEquals(0,$response->data->user->profile->show_avatar->is_nft);

    }


    
}

