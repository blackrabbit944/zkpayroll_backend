<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

// use App\Exceptions\InteractsWithExceptionHandling;


abstract class TestCase extends BaseTestCase {

    // use InteractsWithExceptionHandling;
    use DatabaseMigrations;

    protected $sign_in_user = null;

    function setUp() : void
    {
        parent::setUp();

        // Artisan::call('meilisearch:setup');
        ///不显示exception的错误
        // $this->withoutExceptionHandling();
    }

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {

        $app = require __DIR__.'/../bootstrap/app.php';
 
        // $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
 
        // $this->clearCache(); // NEW LINE -- Testing doesn't work properly with cached stuff.

        return $app;

    }

    public function getVersionUrl($url) {
        $version = 'v1';
        return '/'.$version.$url;
    }

    protected function apiAs($user, $method, $uri, array $data = [], array $headers = [])
    {
        $headers = $this->getJwtHeader($user,$headers);

        return $this->api($method, $uri, $data, $headers);
    }


    protected function api($method, $uri, array $data = [], array $headers = [],$use_version_url = true)
    {
        if ($use_version_url) {
            $uri  =  $this->getVersionUrl($uri);
        }

        $response = $this->call(strtoupper($method),$uri, $data,[],[],$headers);

        // dump('===请求API结果===',$response->getStatusCode());

        if ($response->getStatusCode() == 200) {

            $data = $response->getData();


            if ($data->status == 'success') {
                return $data;
            }else {
                dump('===请求API报错了===');
                dump($data);
                dump('-----');
                return $data;
            }
        }else {

            Log::info('===请求API报错了===');
            Log::info('请求返回'.$response->getStatusCode());
            Log::info('请求URL:'.$uri);

            $data = $response->getData();
            Log::info('请求返回数据'.json_encode($data));
            $response->assertOk();
            return $data;
        }
    }



    protected function apiRequest($method, $uri, array $data = [], array $headers = [],$use_version_url = true)
    {
        if ($use_version_url) {
            $uri  =  $this->getVersionUrl($uri);
        }

        return $this->call(strtoupper($method),$uri, $data,[],[],$headers);

    }

    protected function signIn($user = null)
    {


        if ($this->sign_in_user && !$user) {
            return $this->sign_in_user;
        }

        if (!$user) {            

            $user = User::where('wallet_address', '0x374fEB1050EE9F84d03BE7B189A00c911fD65e2a')->first();
            if (!$user) {
                $faker = \Faker\Factory::create();
                $user = User::factory()->create([
                    'wallet_address'  =>  '0x374fEB1050EE9F84d03BE7B189A00c911fD65e2a',
                ]);
            }
        }

        $this->sign_in_user = $user;
        

        // $this->assertInstanceOf('App\Models\User',$user);
        // $this->assertEquals($user->email,'dreamcog@tinyclub.com');

        // $this->assertDatabaseHas('b_user', [
        //     'email' => 'dreamcog@tinyclub.com',
        // ]);

        $this->actingAs($user,'api');

        return $user;
    }

    public function getJwtHeader($user,array $headers = []) {
        return array_merge([
            'HTTP_AUTHORIZATION' => 'Bearer '.JWTAuth::fromUser($user),
        ], $headers);
    }


    protected function apiWithFileAs($user, $method, $uri, array $data = [],array $files = [], array $headers = [])
    {
        $headers = $this->getJwtHeader($user,$headers);

        return $this->apiWithFile($method, $uri, $data,  $files, $headers);
    }

    protected function apiWithFile($method, $uri, array $data = [],array $files = [], array $headers = [],$use_version_url = true)
    {
        if ($use_version_url) {
            $uri  =  $this->getVersionUrl($uri);
        }
   
        return $this->call(strtoupper($method),$uri, $data,[],$files,$headers);
    }

    public function getPathFromUrl($url) {
        $img_url_arr = explode('/',$url);

        $start = 0;
        $path_arr = [];
        foreach($img_url_arr as $one) {
            if ($start) {
                $path_arr[] = $one;
            }
            if ($one == 'public') {
                $start = 1;
            }
        }
        return implode(DIRECTORY_SEPARATOR, $path_arr);
    }


    protected function uploadAvatar($user) {

        Storage::fake('public');
        ///创建一个模拟文件
        $file = UploadedFile::fake()->image('random.jpg',200,200);
        ///执行上传文件api
        $response = $this->apiWithFileAs($user,'post','/upload/img?template=avatar',[],['file'=>$file])->assertOk()
            ->getData();
        
        ///断言上传成功
        $this->assertEquals('success' , $response->status);

        return $response->data;
    }

    protected function uploadImage($user,$file = null) {

        Storage::fake('public');
        ///创建一个模拟文件
        if (!$file){
            $file = UploadedFile::fake()->image('random.jpg',200,200);
        }
        ///执行上传文件api
        $response = $this->apiWithFileAs($user,'post','/upload/img?template=post_image',[],['file'=>$file])->assertOk()
            ->getData();
        
        ///断言上传成功
        $this->assertEquals('success' , $response->status);

        return $response->data;
    }

   
}
