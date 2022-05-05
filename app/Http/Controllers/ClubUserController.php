<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClubUser;
use App\Models\Club;

use App\Http\Requests\ClubUserRequest;

// use App\Repositories\ClubUserRepository;
// use App\Repositories\DraftClubUserRepository;

use App\Helpers\Hash;
use App\Helpers\Str;
// use App\Helpers\Club;

use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Gate;

use Carbon\Carbon;


class ClubUserController extends Controller
{


    /**
     * HoleController constructor.
     * @param $holeRepository
     */
    public function __construct()
    {
    }

    public function load(ClubUserRequest $request) {
        $clubUser = ClubUser::withTrashed()->where([
            'club_id'   =>  $request->input('club_id'),
            'user_id'   =>  $request->input('user_id') ? $request->input('user_id') : auth('api')->user()->user_id,
        ])->first();
        if (!$clubUser) {
            return $this->failed('用户不存在');
        }
        $clubUser->club;
        return $this->success($clubUser);
    }


    public function list(ClubUserRequest $request) {

        $page_size = get_page_size($request);


        if ($request->input('club_id')) {
            $club = Club::find($request->input('club_id'));
            if (!$club) {
                return $this->failed('club is not exits');
            }
        }
                
        if ($request->user()->cannot('viewAny', [ClubUser::class,$request->all()])) {
            return $this->failed('you have no access for view club user');
        }

        if ($request->input('kw') && $request->input('club_id')) {
            
            $search = ClubUser::search($request->input('kw'))->where('contract_address',$club->contract_address);

            if ($request->input('is_fav') !== null) {
                $search->where('is_fav',$request->input('is_fav'));
            }
            if ($request->input('is_join') !== null) {
                $search->where('is_join',$request->input('is_join'));
            }
            $data = $search->paginate($page_size);

        }else {

            $cond = [];

            if ($request->input('club_id')) {
                $cond = [
                    'contract_address'  =>  $club->contract_address
                ];
            }
            
            if ($request->has('is_join')) {
                if ($request->input('is_join')) {
                    $cond[] = [
                        'join_time',
                        '>',
                        0
                    ];
                    $cond[] = [
                        'left_time',
                        '=',
                        0
                    ];
                }else {
                    $cond[] = [
                        'join_time',
                        '=',
                        0
                    ];
                }
            }else if ($request->has('is_left')) {
                if ($request->input('is_left')) {
                    $cond[] = [
                        'left_time',
                        '>',
                        0
                    ];
                }else {
                    $cond[] = [
                        'left_time',
                        '=',
                        0
                    ];
                }
            }

            
            $order = get_order_by($request);

            $data = ClubUser::where($cond)->orderBy($order[0],$order[1])->paginate($page_size);

        }


        $data->getCollection()->transform(function ($value) {
            $value->user;
            if ($value->user) {
                $value->user->discord_user;
            }
            return $value;
        });

        return $this->success($data);

    }

    // public function listByCond(ClubUserRequest $request) {

    //     if ($request->input('ids')) {
            
    //         ///如果传入ids
    //         $id_arr = explode(',',$request->input('ids'));

    //         $cache_hash = Hash::md5_array($request->only([
    //             'ids','club_id','order_by'
    //         ]));

    //         $query = ClubUser::cacheFor(86400 * 7)
    //             ->cacheTags(['club_user_list:'.$cache_hash])
    //             ->where('club_id',$request->input('club_id'))
    //             ->whereIn('user_id',$id_arr);

    //         if ($request->input('order_by')) {
    //             list($order_key, $order_way)  = Str::getOrderBy($request->input('order_by'));
    //             $query = $query->orderBy($order_key, $order_way);
    //         }


    //     }else {

    //         ///如果传入条件
    //         $cache_hash = Hash::md5_array($request->only([
    //             'is_fav','create_time','reputation','order_by'
    //         ]));

    //         $club_id = $request->input('club_id');

    //         $query = ClubUser::cacheFor(600)
    //             ->cacheTags(['club_user_list:'.$cache_hash])
    //             ->where('club_id',$club_id);

    //         // $query = ClubUser::dontCache();
    //         if ($request->input('user_type') == 'admin'){ 
    //             $user_ids = Club::getRoleUserIds($club_id,'admin',false);
    //             $query = $query->whereIn('user_id',$user_ids);
    //         }

    //         if ($request->input('is_join')) {
    //             $query = $query->where('is_join',$request->input('is_join'));
    //         }

    //         if ($request->input('is_fav')) {
    //             $query = $query->where('is_fav',$request->input('is_fav'));
    //         }

    //         if ($request->input('reputation')) {
    //             extract(json_decode($request->input('reputation'),true));
    //             $method_sign = Str::getSignByMethod($method);
    //             $query = $query->where('reputation',$method_sign,$value);

    //         }

    //         if ($request->input('create_time')) {
    //             extract(json_decode($request->input('create_time'),true));
    //             $method_sign = Str::getSignByMethod($method);
    //             $query = $query->where('create_time',$method_sign,$value);
    //         }

    //         if ($request->input('order_by')) {
    //             list($order_key, $order_way)  = Str::getOrderBy($request->input('order_by'));
    //             $query = $query->orderBy($order_key, $order_way);
    //         }

    //     }
        
    //     $data = $query->with('user')->paginate(get_page_size($request));

    //     // $data->items()->fresh('user');


    //     return $this->success($data);

    // }


}
