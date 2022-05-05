<?php

use App\Models\Post;
use App\Models\Club;
use App\Models\User;
use App\Models\ClubUser;
use App\Models\ClubRole;
use App\Models\Feed;

use Illuminate\Support\Str;
use App\Testutils\DraftUtils;

class PostControllerTest extends TestCase
{

    public function _add() {
        $faker = Faker\Factory::create();
        $fake_text = DraftUtils::fakeContent();
        $title = $faker->title . '?';

        $user = User::factory()->create();
        $club = Club::factory()->create(['user_id'=>$user->user_id]);

        $this->signIn($user);

        // $clubuser = ClubUser::factory()->create(['user_id'=>$user->user_id,'club_id'=>$club->club_id,'feeling'=>'super_like','reputation'=>30]);

        $response = $this->api('post','/post/add',[
            'content'       =>  $fake_text,
            'title'         =>  $title,
            'club_id'       =>  $club->id,
            // 'tags'          =>  implode(',', $faker->words(3,false))
        ]);

        return [
            'user'      =>  $user,
            'club'      =>  $club,
            'response'  =>  $response
        ];
    }
 
    /** @test */
    public function f_post_add()
    {

        $user = User::factory()->create();
        $club = Club::factory()->create(['user_id'=>$user->user_id]);

        $faker = Faker\Factory::create();
        $fake_text = DraftUtils::fakeContent();
        $title = $faker->title . '?';

        //1.非管理员用户不允许添加
        $user2 = User::factory()->create();
        $this->signIn($user2);

        $response = $this->apiRequest('post','/post/add',[
            'content'       =>  $fake_text,
            'title'         =>  $title,
            'club_id'       =>  $club->club_id,
        ])->assertStatus(422);

        // ///2.管理员可以添加
        $this->signIn($user);
        $response = $this->api('post','/post/add',[
            'content'       =>  $fake_text,
            'title'         =>  $title,
            'club_id'       =>  $club->id,
        ]);

        $this->assertEquals($fake_text,$response->data->draft_content->content);
        $this->assertEquals($title,$response->data->title);


    }

    /** @test */
    public function f_post_delete()
    {
        $r = $this->_add();
        $response = $r['response'];

        $response2 = $this->api('delete','/post/delete',[
            'post_id' =>  $response->data->id
        ]);
        $this->assertEquals('success',$response2->status);


        //测试超级管理员可以删除
        $r = $this->_add();
        $response = $r['response'];

        $user1 = User::factory()->create(['is_super_admin'=>1]);
        $this->signIn($user1);

        $response2 = $this->api('delete','/post/delete',[
            'post_id' =>  $response->data->id
        ]);
        $this->assertEquals('success',$response2->status);

        //测试club的管理员可以删除这个
        $r = $this->_add();
        $response = $r['response'];

        $this->signIn($r['user']);
        $response2 = $this->api('delete','/post/delete',[
            'post_id' =>  $response->data->id
        ]);
        $this->assertEquals('success',$response2->status);

        // $this->notSeeInDatabase('b_feed', [
        //     'item_id' => $response->data->post_id,
        //     'item_type' => 'post',
        //     'delete_time'=>null
        // ]);
    }


    /** @test */
    public function f_post_update()
    {
        $faker = Faker\Factory::create();
        $fake_text = DraftUtils::fakeContent();
        $fake_text2 = DraftUtils::fakeContent();
        $title = $faker->title . '?';

        $user = User::factory()->create();
        $club = Club::factory()->create(['user_id'=>$user->user_id]);

        $this->signIn($user);

        $response = $this->api('post','/post/add',[
            'content'       =>  $fake_text,
            'title'         =>  $title,
            'club_id'       =>  $club->id,
        ]);

        ///1.自己可以修改
        $fake_title2 = $faker->title . '?';
        $response2 = $this->api('patch','/post/update',[
            'content'       =>  $fake_text2,
            'post_id'       =>  $response->data->id,
            'title'         =>  $fake_title2,
        ]);
        $this->assertEquals('success',$response2->status);
        $this->assertEquals($fake_title2,$response2->data->title);


        ///2.管理员可以修改
        $user2 = User::factory()->create(['is_super_admin'=>1]);

        $this->signIn($user2);
        $fake_title2 = $faker->title . '?';
        $response2 = $this->api('patch','/post/update',[
            'content'       =>  $fake_text2,
            'post_id'   =>  $response->data->id,
            'title'         =>  $fake_title2
        ]);
        $this->assertEquals('success',$response2->status);
        $this->assertEquals($fake_title2,$response2->data->title);
        $this->assertEquals($fake_text2,$response2->data->draft_content->content);

    }


    /** @test */
    public function f_post_list()
    {
        $club = Club::factory()->create();

        ////1.创建3个问题，能顺利获得
        Post::factory()->create(['club_id'=>$club->id]);
        Post::factory()->create(['club_id'=>$club->id]);
        Post::factory()->create(['club_id'=>$club->id]);

        $response = $this->api('get','/post/list',[
            'club_id'       =>  $club->id
        ]);

        $this->assertCount(3,$response->data->data);

        ////2.检查通过kw来搜索的情况
        $club = Club::factory()->create();
        $user = User::factory()->create();

        Post::factory()->create(['club_id'=>$club->id,'title'=>'提出一个好问题是一个基础的事情?']);
        Post::factory()->create(['club_id'=>$club->id,'title'=>'请问百度有什么问题？']);
        Post::factory()->create(['club_id'=>$club->id,'title'=>'请问知乎有什么问题？','user_id'=>$user->user_id]);

        ///等待2秒，因为meilisearch处理可能需要时间
        // sleep(2);

        $response = $this->api('get','/post/list',[
            'club_id'       =>  $club->id,
        ]);
        $this->assertCount(3,$response->data->data);

    }


    
    /** @test */
    public function f_post_load()
    {
        $r = $this->_add();
        $response = $r['response'];

        $response2 = $this->api('get','/post/load',[
            'post_id'       =>  $response->data->id

        ]);

        $this->assertEquals('success',$response2->status);
        $this->assertEquals($response2->data->id,$response->data->id);
    }




}

