<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserAnalytics;

use App\Helpers\Collection as CollectionHelper;

use App\Http\Requests\UserAnalyticsRequest;
use App\Services\UserService;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Gate;

use Carbon\Carbon;


class UserAnalyticsController extends Controller
{

    public function set(UserAnalyticsRequest $request) {

        $attributes = $request->only([
            'invite_address',
        ]);

        $user = auth('api')->user();

        $row = UserAnalytics::where(['wallet_address'=>$user->wallet_address])->first();
        if ($row && $row->invite_address) {
            return $this->failed('invite_address can only be set once');
        }

        if ($request->input('invite_address') == $user->wallet_address) {
            return $this->failed('invite_address can not be myself');
        }

        $row = UserService::setAnalytics($user->wallet_address,'invite_address',$request->input('invite_address'));
    
        return $this->success($row);
    }   

    public function load(UserAnalyticsRequest $request) {

        $user = auth('api')->user();

        $row = UserAnalytics::where(['wallet_address'=>$user->wallet_address])->first();
        if (!$row) {
            return $this->failed('invite_address not set');
        }

        return $this->success($row);
    }   

    public function list(UserAnalyticsRequest $request) {
        $page_size = get_page_size($request);

        $data = UserAnalytics::where([
            'invite_address'     =>  auth('api')->user()->wallet_address,
        ])->paginate($page_size);

        return $this->success($data);
    }


    public function whitelistData(UserAnalyticsRequest $request) {
        
        ///1.普通卡总计多少个，已经有多少个
        ///2.黑金卡总计多少个，已经有多少个
        ///3.我邀请的用户数量，我的成交数据。
        ///4.最新的获得白名单用户列表
        ///5.最新获得黑金卡白名单用户列表
        $normal_nft = 10000;
        $blackgold_nft = 10000;

        $user = auth('api')->user();
        if ($user) {
            $row = UserAnalytics::where(['wallet_address'=>$user->wallet_address])->first();
        }else {
            $row = null;
        }

        $list_1 = UserAnalytics::select('wallet_address')->where(['is_wl'=>1])->orderBy('update_time','desc')->limit(20);

        $normal_wl_count = UserAnalytics::where(['is_wl'=>1])->count();
        $bg_wl_count = UserAnalytics::where(['is_blackgold_wl'=>1])->count();


        $list_2 = UserAnalytics::select('wallet_address')->where(['is_blackgold_wl'=>1])->orderBy('update_time','desc')->limit(20);


        return $this->success([
            'normal_wl_count'   =>  $normal_wl_count,
            'bg_wl_count'       =>  $bg_wl_count,
            'normal_wl_list'    =>  $list_1,
            'bg_wl_list'        =>  $list_2,
            'my_data'           =>  $row
        ]);
    }


}
