<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAnalytics;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Exceptions\ProgramException;
use App\Exceptions\ApiException;

use Carbon\Carbon;
use Illuminate\Http\Request;

class UserService 
{
    static public function setAnalytics($address,$key,$value) {

        $allow = false;
        switch($key) {
            case 'has_order':
            case 'has_buy_record':
            case 'has_sell_record':
            case 'invite_address':
            case 'is_wl':
            case 'is_blackgold_wl':
                $allow = true;
                break;
            default:

        }

        if (!$allow) {
            Log::debug('传入的用户要记录的数据不在允许范围内，传入的key是'.$key);
            return false;
        }

        $row = UserAnalytics::where(['wallet_address'=>$address])->first();

        if (!$row) {
            $row = UserAnalytics::create([
                'wallet_address'    =>  $address
            ]);
        }

        $v = [];
        $v[$key] = $value;
        $row->fill($v)->save();

        return $row;
    }


    static public function setAnalyticsAdd($address,$key,$add_value) {

        $allow = false;
        switch($key) {
            case 'invite_count':
            case 'sell_volume':
            case 'buy_volume':
                $allow = true;
                break;
            default:

        }

        if (!$allow) {
            Log::debug('传入的用户要记录的数据不在允许范围内，传入的key是'.$key);
            return false;
        }

        $row = UserAnalytics::where(['wallet_address'=>$address])->first();

        if (!$row) {
            $row = UserAnalytics::create([
                'wallet_address'    =>  $address
            ]);
        }

        $old_value = $row->{$key};
        Log::debug('老的数据是:'.$old_value);
        Log::debug('准备添加数据是:'.$add_value);

        $new_v = bcadd($old_value,$add_value,18);
        Log::debug('新的数据是:'.$new_v);


        $v = [];
        $v[$key] = $new_v;
        $row->fill($v)->save();

        ///检查是否达到阈值
        switch($key) {  
            case 'invite_count':
                $check_v = config('app.whitelist.require_invite_user');
                Log::debug('最低要求邀请的用户是:'.$check_v);
                if ($new_v >= $check_v && $row->is_wl == 0)  {
                    Log::debug('检查这个用户已经达到了wl的阈值:'.$new_v);
                    self::setAnalytics($address,'is_wl',1);
                }
                break;

            case 'sell_volume':
                if ($row->has_sell_record == 0) {
                    UserService::setAnalytics($address,'has_sell_record',1);
                }
                $check_v = config('app.whitelist.require_trade_volume');

                Log::debug('检查阈值yuzhi是:'.$check_v);
                Log::debug('检查阈值结果:'.bccomp($new_v,$check_v,18));

                if (bccomp($new_v,$check_v,18) == 1) {
                    Log::debug('检查这个用户已经达到了blackgold_wl的阈值:'.$new_v);
                    self::setAnalytics($address,'is_blackgold_wl',1);
                }
                break;

            case 'buy_volume':
                if ($row->has_buy_record == 0) {
                    UserService::setAnalytics($address,'has_buy_record',1);
                }
                break;

            default:

        }
        return $row;

    }

    static public function setInviteAddress(User $user,$from_address) {

        if (strtolower($user->wallet_address) == strtolower($from_address)) {
            return false;
        }

        $row = UserAnalytics::where(['wallet_address'=>$user->wallet_address])->first();
        if ($row && $row->invite_address) {
            return false;
        }


        self::setAnalytics($user->wallet_address,'invite_address',$from_address);
        self::setAnalyticsAdd($from_address,'invite_count',1);
    }

    /*
        添加到whitelist中
     */
    static function addWhitelist(User $user) {

    }
}
