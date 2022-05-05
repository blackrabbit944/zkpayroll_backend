<?php

use App\Models\User;
use App\Models\Club;

use Illuminate\Support\Facades\DB;

class ClubControllerTest extends TestCase
{

    /** @test */
    public function f_club_add()
    {

        ///1.任何用户创建一个ITEM
        $user = User::factory()->create();
        $this->signIn($user);

        $img = $this->uploadImage($user);

        $faker = Faker\Factory::create();
        $data = [
            'name'          =>  'my club',
            'bio'           =>  $faker->sentence(),
            'avatar_img_id' =>  $img->img_id,
            'unique_name'   =>  'new1',
        ];
        $response = $this->api('post','/club/add',$data);

        $this->assertEquals($data['name'],$response->data->name);
        $this->assertEquals($data['bio'],$response->data->bio);
        $this->assertEquals($data['unique_name'],$response->data->unique_name);

    }


    /** @test */
    public function f_club_update() {

        $faker = Faker\Factory::create();

        ///1.任何用户创建一个ITEM
        $user = User::factory()->create();
        $this->signIn($user);

        $img = $this->uploadImage($user);

        $faker = Faker\Factory::create();
        $club = Club::factory()->create([
            'user_id'       =>  $user->user_id,
            'name'          =>  'my club',
            'bio'           =>  $faker->sentence(),
            'avatar_img_id' =>  $img->img_id,
            'unique_name'   =>  'new1'
        ]);

        $data = [
            'id'            =>  $club->id,
            'name'          =>  'my instagram2',
            'bio'           =>  $faker->sentence(),
            'unique_name'   =>  'new2'
        ];
        $response = $this->api('patch','/club/update',$data);
        $this->assertEquals($data['name'],$response->data->name);
        $this->assertEquals($data['bio'],$response->data->bio);
        $this->assertEquals($data['unique_name'],$response->data->unique_name);

        $user2 = User::factory()->create();
        $this->signIn($user2);

        $data = [
            'id'            =>  $club->id,
            'name'          =>  'my instagram3',
        ];
        $response = $this->apiRequest('patch','/club/update',$data)->assertStatus(400);

    }

    /** @test */
    public function f_club_delete()
    {
        $faker = Faker\Factory::create();

        $user = User::factory()->create();
        $this->signIn($user);

        $faker = Faker\Factory::create();
        $club = Club::factory()->create([
            'user_id'       =>  $user->user_id,
            'name'          =>  'my club',
            'bio'           =>  $faker->sentence(),
            'unique_name'   =>  'new1'
        ]);


        ///1.删除者是当前用户
        $data = [
            'id'            =>  $club->id,
        ];
        $response = $this->api('delete','/club/delete',$data);
        $this->assertEquals('success',$response->status);

        ///2.删除者不是创建者
        $club2 = Club::factory()->create([
            'user_id'       =>  $user->user_id,
            'name'          =>  'my club2',
            'bio'           =>  $faker->sentence(),
            'unique_name'   =>  'new2'
        ]);

        $user2 = User::factory()->create();
        $this->signIn($user2);

        $data = [
            'id'            =>  $club2->id,
        ];
        $response = $this->apiRequest('delete','/club/delete',$data)->assertStatus(400);;
        

    }

    /** @test */
    public function f_club_load() {
        $faker = Faker\Factory::create();

        $user = User::factory()->create();
        $this->signIn($user);
        $img = $this->uploadImage($user);

        $faker = Faker\Factory::create();
        $club = Club::factory()->create([
            'user_id'       =>  $user->user_id,
            'name'          =>  'my club',
            'bio'           =>  $faker->sentence(),
            'avatar_img_id' =>  $img->img_id,
            'unique_name'   =>  'new1'
        ]);

        $data = [
            'id'                  =>  $club->id
        ];
        $response2 = $this->api('get','/club/load',$data);
        $this->assertEquals($club->id,$response2->data->id);
        $this->assertEquals($club->name,$response2->data->name);


    }

    /** @test */
    public function f_club_list() {

        ///1.任何用户创建一个ITEM
        $user = User::factory()->create();
        $this->signIn($user);

        $faker = Faker\Factory::create();

        ///2.如果一个人创建超过了100个club则会报错
        $clubs = Club::factory()->count(40)->create(['user_id'=>$user->user_id]);

        $data = [
            'address'       =>  $user->wallet_address,
        ];
        $response = $this->api('get','/club/list',$data);
        $this->assertCount(40,$response->data);
    }

}

