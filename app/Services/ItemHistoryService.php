<?php

namespace App\Services;

use App\Models\ItemHistory;
// use App\Helpers\Moralis;
// use App\Helpers\Alchemy;

// use App\Events\CreateItemEvent;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

use App\Exceptions\ProgramException;
use App\Exceptions\ApiException;

use Carbon\Carbon;
use Illuminate\Http\Request;

// use App\Helpers\Image as ImageHelper;

class ItemHistoryService 
{
    static public function create($data = []) {

        $item = ItemService::create([
            'contract_address'     =>  $data['contract_address'],
            'token_id'             =>  $data['token_id'],
        ]);

        $data['item_id'] =  $item->id;


        Log::debug('准备创建ItemHistory数据:'.json_encode($data));

        $item_history = ItemHistory::create($data);

        Log::debug('创建成功:'.json_encode($item_history->toArray()));

        return $item_history;
    }


}
