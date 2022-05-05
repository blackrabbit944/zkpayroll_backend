<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\InitRequest;
use App\Models\Order;
use App\Models\Club;

use Illuminate\Support\Collection;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Cache;

use Carbon\Carbon;


class InitController extends Controller
{

    public function loginUser(InitRequest $request) {

        $login_user = auth('api')->user();
        
        if ($login_user) {
            $login_user->makeVisible(['unique_hash']);
            $login_user->discord_user;
        }


        return $this->success([
            'login_user'    =>  $login_user,
        ]);

    }

    public function statistic(InitRequest $request) {

       
        $status = Cache::remember('website_status',15,function() {

            Log::debug('debug,没有命中缓存:website_status');

            $club_count = Club::where('total_mint_income', '>' , 0)->count();
            $total_mint_income = Club::where('total_mint_income', '>' , 0)->sum('total_mint_income');

            $each_club_income  = 0;
            if ($club_count > 0) {
                $each_club_income = bcdiv($total_mint_income,$club_count,6);
            }


            return [
                'club_count'        =>  $club_count,
                'total_mint_income' =>  $total_mint_income,
                'each_club_income'  =>  $each_club_income,
                'time'              =>  time()
            ];

        });



        return $this->success($status);
    }

}
