<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemHistory;
use App\Models\Category;

use App\Helpers\ItemHistory as ItemHistoryHelper;

use App\Http\Requests\ItemHistoryRequest;

use App\Services\ItemHistoryService;

use App\Events\FetchTokenInfoEvent;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Response;

use Carbon\Carbon;


class ItemHistoryController extends Controller
{


    public function load(ItemHistoryRequest $request) {

        if ($request->input('id')) {
            $item = ItemHistory::find($request->input('id'));
        }else {
            $item = ItemHistory::where([
                'contract_address'  =>  $request->input('contract_address'),
                'token_id'          =>  $request->input('token_id'),
            ])->first();
        }

        if (!$item) {
            return $this->failure('item is not exist');
        }
        
        $item->format();

        return $this->success($item);
    }


    public function list(ItemHistoryRequest $request) {

        $page_size = get_page_size($request);

        if ($request->input('order_by')) {
            $order = get_order_by($request);
        }else {
            $order = ['action_time','asc'];
        }

        $cond = [];
        $cond = $request->only([
            'contract_address',
            'token_id',
            'action_name'
        ]);

        $data = ItemHistory::where($cond)->orderby($order[0],$order[1])->paginate($page_size);


        $data->getCollection()->transform(function ($value) {
            $value->append('item');
            $row = $value->toArray();
            if ($row['item']) {
                $row['item']['collection'] = $value->item->collection->toArray();
            }
            return $row;
        });

        return $this->success($data);

    }



}
