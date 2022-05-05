<?php

use App\Models\User;
use App\Models\Link;

use Illuminate\Support\Facades\DB;

class VisitDataControllerTest extends TestCase
{

    /** @test */
    public function f_visit_data_add()
    {

        ///1.任何用户创建一个ITEM
        // $user = User::factory()->create();
        // $this->signIn($user);

        // $faker = Faker\Factory::create();

        // $data = [
        //     'text'          =>  'my instagram',
        //     'show_type'     =>  'button',
        //     'url'           =>  $faker->url
        // ];
        // $response = $this->api('post','/link/add',$data);

        // ///2.如果一个人创建超过了100个link则会报错
        // $links = Link::factory()->count(99)->create(['user_id'=>$user->user_id]);

        // $data = [
        //     'text'          =>  'my instagram test',
        //     'show_type'     =>  'button',
        //     'url'           =>  $faker->url
        // ];
        // $this->apiRequest('post','/link/add',$data)->assertStatus(400);;

    }


    /** @test */
    public function f_visit_data_analytics() {

        // $faker = Faker\Factory::create();

        // ///1.任何用户创建一个ITEM
        // $user = User::factory()->create();
        // $this->signIn($user);

        // $link = Link::factory()->create([
        //     'user_id'       =>  $user->user_id,
        //     'text'          =>  'my instagram',
        //     'show_type'     =>  'button',
        //     'url'           =>  $faker->url
        // ]);

        // $data = [
        //     'id'            =>  $link->id,
        //     'text'          =>  'my instagram2',
        //     'show_type'     =>  'icon',
        //     'url'           =>  $faker->url
        // ];
        // $response = $this->api('patch','/link/update',$data);
        // $this->assertEquals($data['text'],$response->data->text);
        // $this->assertEquals($data['show_type'],$response->data->show_type);
        // $this->assertEquals($data['url'],$response->data->url);

        // $user2 = User::factory()->create();
        // $this->signIn($user2);

        // $data = [
        //     'id'            =>  $link->id,
        //     'text'          =>  'my instagram3',
        // ];
        // $response = $this->apiRequest('patch','/link/update',$data)->assertStatus(400);

    }

}

