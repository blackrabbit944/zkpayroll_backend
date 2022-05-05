<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DiscordLog;
use App\Models\Club;

use App\Http\Requests\DiscordLogRequest;

// use App\Repositories\DiscordLogRepository;
// use App\Repositories\DraftDiscordLogRepository;

use App\Helpers\Hash;
use App\Helpers\Str;
// use App\Helpers\Club;

use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Gate;

use Carbon\Carbon;


class DiscordLogController extends Controller
{


    /**
     * HoleController constructor.
     * @param $holeRepository
     */
    public function __construct()
    {
    }

    public function load(DiscordLogRequest $request) {
        $DiscordLog = DiscordLog::withTrashed()->where([
            'club_id'   =>  $request->input('club_id'),
            'user_id'   =>  $request->input('user_id') ? $request->input('user_id') : auth('api')->user()->user_id,
        ])->first();
        if (!$DiscordLog) {
            return $this->failed('用户不存在');
        }
        $DiscordLog->club;
        return $this->success($DiscordLog);
    }


    public function list(DiscordLogRequest $request) {

        $page_size = get_page_size($request);

        if ($request->input('club_id')) {
            $club = Club::find($request->input('club_id'));
            if (!$club) {
                return $this->failed('club is not exits');
            }
        }
                
        if ($request->user()->cannot('viewAny', [DiscordLog::class,$request->all()])) {
            return $this->failed('you have no access for view club user');
        }

        $cond = $request->only(['club_id','action_type']);
        $order = get_order_by($request,'id_desc');

        $data = DiscordLog::where($cond)->orderBy($order[0],$order[1])->paginate($page_size);



        $data->getCollection()->transform(function ($value) {
            $value->discord_user;
            return $value;
        });

        return $this->success($data);

    }



}
