<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

use App\Services\UserService;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Events\AddUserDefaultAvatarEvent;

use PHPOpenSourceSaver\JWTAuth;

use Illuminate\Auth\Passwords\PasswordBrokerManager;
use Illuminate\Support\Facades\Password;
// use Laravel\Lumen\Routing\Controller;

use App\Helpers\EthSign;
use App\Exceptions\ApiException;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }
  

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(UserRequest $request)
    {

        auth('api')->invalidate();

        // auth('api')->logout();
        
        return $this->success('logout success');
    }


    public function load(UserRequest $request) {

        $user = User::find($request->input('user_id'));
        $user->format();

        $user->profile;
        // dd($user->toArray());

        return $this->success($user);
    }


    private function checkWalletSign(UserRequest $request, $action_name) {
        $signHelper = new EthSign();
        $result = $signHelper->check($request->input('sign'),$request->input('address'),$request->input('params'),$action_name);

        if (!$result) {
            $error = $signHelper->getErrorMessage();
            throw new ApiException($error);
        }

        return true;
    }


    /*
    *   web3登录的逻辑如下
    *   1.验证签名字符串中，是否符合简答的规则，签名的create_time是否大于最后一次登录通过的create_time
    *   2.先验证用户传入的sign，钱包地址，签名字符串，三个是否匹配，这个部分用Grpc调度nodejs完成
    *   3.确认的话，允许用户登录，如果用户没有注册过用户，则自动注册用户，并走注册流程。
    *   4.用户注册过以后的话，则走登录流程
     */
    public function login(UserRequest $request) {

        // if (!app()->environment('local')) {
            $this->checkWalletSign($request,'login');
        // }
        // 
        $signHelper = new EthSign();
        $attributes = $signHelper->getMessage($request->input('params'));

        $user = User::where(['wallet_address'=>$attributes['wallet_address']])->first();

        if (!$user) {
            $user = User::create(['wallet_address'=>$attributes['wallet_address']]);
        }

        ///登录这个用户
        $token = auth('api')->setTTL(config('jwt.ttl'))->login($user);

        if (!$token) {
            return $this->failed('cannot genarate user token');
        }

        ///处理用户邀请的逻辑
        // if ($request->input('source_from')) {
        //     $from_user = User::find($request->input('source_from'));
        //     if ($from_user) {
        //         Log::debug('找到了邀请来源的用户是:'.$from_user->user_id);
        //         UserService::setInviteAddress($user,$from_user->wallet_address);
        //     }
        // }


        return $this->success([
            'login_user'=>$user,
            'jwt_token'=>$token
        ]);

    }

    public function fakeLogin(UserRequest $request) {
        
        if (!app()->environment('local')) {
            return $this->failed('cannot use');
        }

        $user = User::where(['wallet_address'=>$request->input('address')])->first();

        if (!$user) {
            $user = User::create(['wallet_address'=>$request->input('address')]);
        }

        ///登录这个用户
        $token = auth('api')->setTTL(config('jwt.ttl'))->login($user);

        if (!$token) {
            return $this->failed('cannot genarate user token');
        }

        return $this->success([
            'login_user'=>$user,
            'jwt_token'=>$token
        ]);

    }


}
