<?php

use App\Models\User;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SteamingSalaryControllerTest extends TestCase
{

    private function create() {
        $user = User::factory()->create();
        $this->signIn($user);

        $data = [
            'contract_address'  =>  '0x822ca080e094bf068090554a19bc3d6618c800b3',
            'name'              =>  'jackma',
            'amount'            =>  5000.12,
            'during_time'            =>  30*86400,
            'address'           =>  '0x'.Str::random(40),
        ];
        $response = $this->api('post','/steaming_salary/add',$data);
        return [
            'user'      =>  $user,
            'response'  =>  $response
        ];
    }

    /** @test */
    public function f_steaming_salary_add()
    {
        ///1.创建一个不在salary的contract列表的要被拒绝
        $user = User::factory()->create(['is_super_admin'=>0]);
        $this->signIn($user);
        
        $data = [
            'contract_address'  =>  '0xed5af388653567af2f388e6224dc7c4b3241c544',
            'name'              =>  'jackma',
            'amount'            =>  5000.12,
            'during_time'            =>  30*86400,
            'address'           =>  '0x'.Str::random(40),
        ];
        $this->apiRequest('post','/steaming_salary/add',$data)->assertStatus(422);

        ///2.创建一个存在的salary是可以的
        $user = User::factory()->create();
        $this->signIn($user);

        $data = [
            'contract_address'  =>  '0x822ca080e094bf068090554a19bc3d6618c800b3',
            'name'              =>  'jackma',
            'amount'            =>  5000.12,
            'during_time'            =>  30*86400,
            'address'           =>  '0x'.Str::random(40),
        ];
        $response = $this->api('post','/steaming_salary/add',$data);
        $this->assertEquals('0x822ca080e094bf068090554a19bc3d6618c800b3',$response->data->contract_address);
        $this->assertEquals('jackma',$response->data->name);
        $this->assertEquals(5000.12,$response->data->amount);
        $this->assertEquals(30*86400,$response->data->during_time);
        $this->assertEquals(strtolower($data['address']),strtolower($response->data->address));

        ///数据库是存在的
        $this->seeInDatabase(
            'b_steaming_salary',
            [
                'id' =>  $response->data->id
            ]
        );

        ///4.删除一个salary后重新创建则id不会改变
        $data2 = [
            'id'          =>  $response->data->id,
        ];
        $response2 = $this->api('delete','/steaming_salary/delete',$data2);

        $this->assertEquals('success',$response2->status);

        $data['name'] = 'justinsun';
        $data['amount'] = 100;

        $response3 = $this->api('post','/steaming_salary/add',$data);
        $this->assertEquals($response->data->id,$response3->data->id);
        $this->assertEquals($data['name'],$response3->data->name);
        $this->assertEquals($data['amount'],$response3->data->amount);
        $this->assertEquals($data['during_time'],$response3->data->during_time);

    }

    /** @test */
    public function f_steaming_salary_delete()
    {
        ///1.创建一个salary
        $data = $this->create();

        ///2.别人创建我创建的
        $user2 = User::factory()->create(['is_super_admin'=>0]);
        $this->signIn($user2);

        $data2 = [
            'id'          =>  $data['response']->data->id,
        ];
        $response = $this->apiRequest('delete','/steaming_salary/delete',$data2)->assertStatus(400);

        
        ///2.自己删除自己创建的
        $this->signIn($data['user']);

        $data3 = [
            'id'          =>  $data['response']->data->id,
        ];
        $response = $this->api('delete','/steaming_salary/delete',$data3);
        $this->assertEquals('success',$response->status);
        
    }


    /** @test */
    public function f_steaming_salary_update() {

        $add_response = $this->create();

        $user = User::factory()->create(['is_super_admin'=>0]);
        $this->signIn($user);

        $data = [
            'id'        =>  $add_response['response']->data->id,
            'amount'    =>  10000
        ];
        $response = $this->apiRequest('patch','/steaming_salary/update',$data)->assertStatus(400);

        $this->signIn($add_response['user']);

        $data2 = [
            'id'             =>  $add_response['response']->data->id,
            'amount'         =>  10000,
            'during_time'    =>  86400
        ];
        $response = $this->api('patch','/steaming_salary/update',$data2);


        $this->assertEquals($data2['amount'],$response->data->amount);
        $this->assertEquals($data2['during_time'],$response->data->during_time);

    }


    /** @test */
    public function f_steaming_salary_load()
    {
        ///1.如果一个非管理员是不允许创建salary的
        $add_response = $this->create();

        $data = [
            'id'          =>  $add_response['response']->data->id,
        ];
        $response2 = $this->api('get','/steaming_salary/load',$data);
        $this->assertEquals('jackma',$response2->data->name);
        $this->assertEquals(5000.12,$response2->data->amount);
        $this->assertEquals('0x822ca080e094bf068090554a19bc3d6618c800b3',$response2->data->contract_address);


    }  



    /** @test */
    public function f_steaming_salary_list()
    {
        $user = User::factory()->create();
        $this->signIn($user);

        $data = [];

        $data[] = [
            'contract_address'  =>  '0x822ca080e094bf068090554a19bc3d6618c800b3',
            'name'              =>  'jackma',
            'amount'            =>  5000.12,
            'during_time'            =>  3000,
            'address'           =>  '0x'.Str::random(40),
        ];

        $data[] = [
            'contract_address'  =>  '0x822ca080e094bf068090554a19bc3d6618c800b3',
            'name'              =>  'justinsun',
            'amount'            =>  10000,
            'during_time'            =>  86400,
            'address'           =>  '0x'.Str::random(40),
        ];


        $data[] = [
            'contract_address'  =>  '0x822ca080e094bf068090554a19bc3d6618c800b3',
            'name'              =>  'ponyma',
            'amount'            =>  20000,
            'during_time'            =>  86400,
            'address'           =>  '0x'.Str::random(40),
        ];

        foreach($data as $one) {
            $this->api('post','/salary/add',$one);
        }

        $response2 = $this->api('get','/salary/list',[]);
        $this->assertCount(3,$response2->data);

    }  




}

