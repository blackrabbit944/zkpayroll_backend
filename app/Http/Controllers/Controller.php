<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;

use Illuminate\Support\Facades\Auth;
use App\Helpers\RecaptchaV3;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Controller 
{

    use ApiResponse;
    // 其他通用的Api帮助函数

    
    protected function respondWithToken($token)
    {
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ], 200);
    }


    
    /// 获取 recaptcha 分数，如果返回 null 则表示没有提供 recaptcha token
    public static function getRecaptchaScore(Request $request,$recaptcha_name) {        
        if (!app()->environment(['local','testing'])){
            //验证recaptcha
            if (!$request->input('recaptcha_token')) {
                Log::debug("未提供 recaptcha_token，直接返回 null");
                return null;
            }
            $rank = RecaptchaV3::getRank($recaptcha_name,$request->input('recaptcha_token'));
            if ($rank === false) {
                Log::error("Recaptcha 服务端验证失败，无法获取 recaptcha 分数");
                return null;
            }
            return $rank;
        }else {
            ///测试环境
            $rank = 0.9;

            Log::debug("目前运行在测试环境，则是返回 recaptcha 分数为 {$rank}");
            
            if ($request->input('recaptcha_token')) {
                $rank = RecaptchaV3::getRank($recaptcha_name,$request->input('recaptcha_token'));
                if ($rank === false) {
                    Log::error("Recaptcha 服务端验证失败，无法获取 recaptcha 分数");
                    return null;
                }
            } else {
                return null;
            }

            return $rank;
        }
    }


    // protected function authorize($action,$ext_data) {
    //     $ret = auth()->user()->can($action,$ext_data);

    //     if (!$ret) {
    //         return $this->failed(sprintf('you have no acess for $s action',$action));
    //     }
    // }
}