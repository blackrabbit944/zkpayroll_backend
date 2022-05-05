<?php

use App\Models\User;
use App\Models\Link;

use Illuminate\Support\Facades\DB;

class LinkControllerTest extends TestCase
{

    /** @test */
    public function f_link_add()
    {

        ///1.任何用户创建一个ITEM
        $user = User::factory()->create();
        $this->signIn($user);

        $faker = Faker\Factory::create();

        $data = [
            'text'          =>  'my instagram',
            'show_type'     =>  'button',
            'url'           =>  $faker->url
        ];
        $response = $this->api('post','/link/add',$data);

        ///2.如果一个人创建超过了100个link则会报错
        $links = Link::factory()->count(99)->create(['user_id'=>$user->user_id]);

        $data = [
            'text'          =>  'my instagram test',
            'show_type'     =>  'button',
            'url'           =>  $faker->url
        ];
        $this->apiRequest('post','/link/add',$data)->assertStatus(400);;

    }


    /** @test */
    public function f_link_update() {

        $faker = Faker\Factory::create();

        ///1.任何用户创建一个ITEM
        $user = User::factory()->create();
        $this->signIn($user);

        $link = Link::factory()->create([
            'user_id'       =>  $user->user_id,
            'text'          =>  'my instagram',
            'show_type'     =>  'button',
            'url'           =>  $faker->url
        ]);

        $data = [
            'id'            =>  $link->id,
            'text'          =>  'my instagram2',
            'show_type'     =>  'icon',
            'url'           =>  $faker->url
        ];
        $response = $this->api('patch','/link/update',$data);
        $this->assertEquals($data['text'],$response->data->text);
        $this->assertEquals($data['show_type'],$response->data->show_type);
        $this->assertEquals($data['url'],$response->data->url);

        $user2 = User::factory()->create();
        $this->signIn($user2);

        $data = [
            'id'            =>  $link->id,
            'text'          =>  'my instagram3',
        ];
        $response = $this->apiRequest('patch','/link/update',$data)->assertStatus(400);

    }

    /** @test */
    public function f_link_delete()
    {
        $faker = Faker\Factory::create();

        $user = User::factory()->create();
        $this->signIn($user);

        $link = Link::factory()->create([
            'user_id'       =>  $user->user_id,
            'text'          =>  'my instagram',
            'show_type'     =>  'button',
            'url'           =>  $faker->url
        ]);


        ///1.删除者是当前用户
        $data = [
            'id'            =>  $link->id,
        ];
        $response = $this->api('delete','/link/delete',$data);
        $this->assertEquals('success',$response->status);

        ///2.删除者不是创建者
        $link2 = Link::factory()->create([
            'user_id'       =>  $user->user_id,
            'text'          =>  'my instagram',
            'show_type'     =>  'button',
            'url'           =>  $faker->url
        ]);

        $user2 = User::factory()->create();
        $this->signIn($user2);

        $data = [
            'id'            =>  $link2->id,
        ];
        $response = $this->apiRequest('delete','/link/delete',$data)->assertStatus(400);;
        

    }

    /** @test */
    public function f_link_load() {
        $faker = Faker\Factory::create();

        $user = User::factory()->create();
        $this->signIn($user);

        $link = Link::factory()->create([
            'user_id'       =>  $user->user_id,
            'text'          =>  'my instagram',
            'show_type'     =>  'button',
            'url'           =>  $faker->url
        ]);

        $data = [
            'id'                  =>  $link->id
        ];
        $response2 = $this->api('get','/link/load',$data);
        $this->assertEquals($link->id,$response2->data->id);
        $this->assertEquals($link->text,$response2->data->text);


    }

    /** @test */
    public function f_link_list() {

        ///1.任何用户创建一个ITEM
        $user = User::factory()->create();
        $this->signIn($user);

        $faker = Faker\Factory::create();

        ///2.如果一个人创建超过了100个link则会报错
        $links = Link::factory()->count(40)->create(['user_id'=>$user->user_id]);

        $data = [
            'address'       =>  $user->wallet_address,
        ];
        $response = $this->api('get','/link/list',$data);
        $this->assertCount(40,$response->data);
    }

     /** @test */
     public function f_link_sort() {

        $faker = Faker\Factory::create();

        ///1.任何用户创建一个ITEM
        $user = User::factory()->create();
        $this->signIn($user);

        $link1 = Link::factory()->create([
            'user_id'       =>  $user->user_id,
            'text'          =>  'my instagram',
            'show_type'     =>  'button',
            'url'           =>  $faker->url,
            'sort_id'       => 1
        ]);

        $link2 = Link::factory()->create([
            'user_id'       =>  $user->user_id,
            'text'          =>  'my instagram',
            'show_type'     =>  'button',
            'url'           =>  $faker->url,
            'sort_id'       => 2
        ]);

        $link3 = Link::factory()->create([
            'user_id'       =>  $user->user_id,
            'text'          =>  'my instagram',
            'show_type'     =>  'button',
            'url'           =>  $faker->url,
            'sort_id'       => 3
        ]);

        $arr = [];
        array_push($arr,$link2->id);
        array_push($arr,$link3->id);
        array_push($arr,$link1->id);

        $response = $this->api('post','/link/sort',['ids'=>implode(',',$arr)]);

        $this->assertEquals('success',$response->status);

        $this->seeInDatabase('b_link', [
            'id'            => $link1->id,
            'sort_id'      => 2
        ]);

        $this->seeInDatabase('b_link', [
            'id'            => $link2->id,
            'sort_id'       => 0
        ]);

        $this->seeInDatabase('b_link', [
            'id'            => $link3->id,
            'sort_id'       => 1
        ]);
    }
}

