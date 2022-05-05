<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tx;
use App\Models\Club;

use App\Http\Requests\TxRequest;

use App\Helpers\Hash;
use App\Helpers\Str;
// use App\Helpers\Club;

use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Gate;

use Carbon\Carbon;


class TxController extends Controller
{


    /**
     * HoleController constructor.
     * @param $holeRepository
     */
    public function __construct()
    {
    }

    public function load(TxRequest $request) {
        $Tx = Tx::withTrashed()->where([
            'club_id'   =>  $request->input('club_id'),
            'user_id'   =>  $request->input('user_id') ? $request->input('user_id') : auth('api')->user()->user_id,
        ])->first();
        if (!$Tx) {
            return $this->failed('用户不存在');
        }
        $Tx->club;
        return $this->success($Tx);
    }


    public function list(TxRequest $request) {

        $page_size = get_page_size($request);

        $cond = $request->only(['tx_type']);

        if ($request->input('club_id')) {
            $club = Club::find($request->input('club_id'));
            if (!$club) {
                return $this->failed('club is not exits');
            }
            if (!$club->contract_address) {
                return $this->failed('club contract_address is not exits');
            }
            $cond['contract_address'] = $club->contract_address;
        }else {
            $cond['contract_address'] = $request->input('contract_address');
        }
                
        if ($request->user()->cannot('viewAny', [Tx::class,$cond])) {
            return $this->failed('you have no access for view club txs');
        }

        $order = get_order_by($request);

        $data = Tx::where($cond)->orderBy($order[0],$order[1])->paginate($page_size);

        $data->getCollection()->transform(function ($value) {
            $value->format();
            return $value;
        });

        return $this->success($data);

    }



}
