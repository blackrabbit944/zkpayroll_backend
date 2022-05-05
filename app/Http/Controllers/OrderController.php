<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Category;

use App\Helpers\Order as OrderHelper;

use App\Http\Requests\OrderRequest;

use App\Services\OrderService;

use App\Events\FetchTokenInfoEvent;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Validator;


use App\Rules\ExpireTime;
use App\Rules\EthContractAddress;
use App\Rules\Price;

use Illuminate\Http\Response;

use Carbon\Carbon;

use App\Helpers\EthSign;

class OrderController extends Controller
{

    protected $error_message = null;

    private function validateSign(OrderRequest $request,$action_name) {

        // if (app()->environment(['local', 'testing'])) {
        //     return true;
        // }

        try {
            $ethSignHelper = new EthSign();
            $result = $ethSignHelper->check($request->input('sign'),$request->input('address'),$request->input('params'),$action_name);

            if (!$result) {
                $this->error_message = $ethSignHelper->getErrorMessage();
            }

            return $result;

        }catch (Exception $e) {
            throw new ApiException($e->getMessage());
        }

        ///TODO:签名检查，验证必须是这个wallet的持有者发出来的信息才可信
    }



    private function validateCreateOrderForm($params) {
        dump($params);
    }


    public function validateOwner($params) {        

        
        $erc721 = \App\Helpers\Solidity::getErc721($params['contract_address']);
        
        try {
            $result = $erc721->ownerOf(new \Ethereum\DataType\EthD(\dechex($params['token_id'])));
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if (strpos($msg, '200 OK') !== FALSE) {
                /// API 返回了数据，但是因为合约执行失败，所以返回了合约执行失败的错误信息，这种情况下，说明不存在这个 token_id
                throw new \App\Exceptions\ApiException("指定的 token_id 可能尚未被 mint");
            }
        }

        
        $address_raw = $result->val();

        if (strtolower("0x" . $address_raw) == strtolower($params['wallet_address'])) {
            return true;
        }else {
            return false;
        }

    }

    public function add(OrderRequest $request) {

        if (!$this->validateSign($request,'add_order')) {
            return $this->failed($this->error_message);
        }

        $ethSignHelper = new EthSign();
        $attributes = $ethSignHelper->getMessage($request->input('params'));

        $validator = Validator::make($attributes, [
            'contract_address' => ['required','string',new EthContractAddress],
            'token_id'         => ['required','integer'],
            'price'            => ['required','numeric','gt:0',new Price],
            'expire_time'      => ['required','integer',new ExpireTime],
        ], []);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->failed($messages);
        }

        if (!$this->validateOwner([
            'contract_address'  =>  $attributes['contract_address'],
            'token_id'          =>  $attributes['token_id'],
            'wallet_address'    =>  $attributes['wallet_address']
        ])) {
            return $this->failed('不是所有者');
        }

        if ($request->user()->cannot('create', [Order::class,$attributes])) {
            return $this->failed('you have no access for add order');
        }

        $order = Order::where([
            'contract_address'     =>  $attributes['contract_address'],
            'token_id'             =>  $attributes['token_id'],
        ])->first();

        if ($order) {


            if ($order->from_address == $attributes['wallet_address']) {

                ///1.检查是否已经有过挂单，如果是的话则按照当前价格，结束时间修改订单.
                $new_data = [];
                if ($attributes['expire_time']) {
                    $new_data['expire_time'] = $attributes['expire_time'];
                }
                if ($attributes['price']) {
                    $new_data['price'] = $attributes['price'];
                }

                $order->fill($new_data);
                $order->save();
                return $this->success($order);

            }else {
                $order->delete();
            }
        }


        ///2.如果没有的话，则创建一个挂单。
        $orderService = new OrderService();
        $order = $orderService->create($attributes);

        return $this->success($order);
 
    }


    public function update(OrderRequest $request) {

        if (!$this->validateSign($request,'update_order')) {
            return $this->failed($this->error_message);
        }

        $ethSignHelper = new EthSign();
        $attributes = $ethSignHelper->getMessage($request->input('params'));

        $validator = Validator::make($attributes, [
            'contract_address' => ['required','string',new EthContractAddress],
            'token_id'         => ['required','integer'],
            'price'            => ['required','numeric','gt:0',new Price],
        ], []);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->failed($messages);
        }

        if (!$this->validateOwner([
            'contract_address'  =>  $attributes['contract_address'],
            'token_id'          =>  $attributes['token_id'],
            'wallet_address'    =>  $attributes['wallet_address']
        ])) {
            return $this->failed('不是所有者');
        }

        $order = Order::where([
            'contract_address'     =>  $attributes['contract_address'],
            'token_id'             =>  $attributes['token_id'],
            'from_address'         =>  $attributes['wallet_address'],
        ])->first();

        if (!$order) {
            return $this->failed('Orders do not exist or have been filled');
        }

        if ($request->user()->cannot('update', $order)) {
            return $this->failed('you have no access for add order');
        }

        if ($attributes['price']) {
            $order->fill([
                'price' => $attributes['price']
            ]);
        }

        $order->save();
        
        $order->fresh();
        $order->format();

        return $this->success($order);

    }

    public function delete(OrderRequest $request) {

        if (!$this->validateSign($request,'cancel_order')) {
            return $this->failed($this->error_message);
        }

        $ethSignHelper = new EthSign();
        $attributes = $ethSignHelper->getMessage($request->input('params'));

        $validator = Validator::make($attributes, [
            'contract_address' => ['required','string',new EthContractAddress],
            'token_id'         => ['required','integer'],
        ], []);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->failed($messages);
        }

        $order = Order::where([
            'contract_address'     =>  $attributes['contract_address'],
            'token_id'             =>  $attributes['token_id'],
            'from_address'         =>  $attributes['wallet_address'],
        ])->first();

        if (!$order) {
            return $this->failed('Orders do not exist or have been filled');
        }

        Log::debug('检查到准备撤销的订单id是:'.$order->id);

        if (!$order) {
            return $this->failed('Orders do not exist or have been filled');
        }

        if ($request->user()->cannot('delete', $order)) {
            return $this->failed('you have no access for add order');
        }
        Log::debug('用户权限检查完毕准备删除订单:'.$order->id);

        $order->delete();

        return $this->success('');
    }



    public function deleteByOwner(OrderRequest $request) {

        $attributes = $request->only(['contract_address','token_id']);
        $user = auth('api')->user();

        $validator = Validator::make($attributes, [
            'contract_address' => ['required','string',new EthContractAddress],
            'token_id'         => ['required','integer'],
        ], []);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->failed($messages);
        }

        if (!$this->validateOwner([
            'contract_address'  =>  $attributes['contract_address'],
            'token_id'          =>  $attributes['token_id'],
            'wallet_address'    =>  $user->wallet_address
        ])) {
            return $this->failed('不是所有者');
        }


        $order = Order::where([
            'contract_address'     =>  $attributes['contract_address'],
            'token_id'             =>  $attributes['token_id'],
        ])->first();

        if (!$order) {
            return $this->failed('Orders do not exist or have been filled');
        }

        Log::debug('检查到准备撤销的订单id是:'.$order->id);

        if ($order && $order->from_address != $user->wallet_address) {
            $order->delete();
        }

        return $this->success('');
    }


    public function load(OrderRequest $request) {

        if ($request->input('id')) {
            $order = Order::find($request->input('id'));
        }else {
            $order = Order::where([
                'contract_address'  =>  $request->input('contract_address'),
                'token_id'          =>  $request->input('token_id'),
            ])->first();
        }

        if (!$order) {
            return $this->failed('order is not exist');
        }
        
        $order->format();

        return $this->success($order);
    }

    public function List(OrderRequest $request) {
        
        $page_size = get_page_size($request);
        $order_by = get_order_by($request);

        $cond=[];

        if ($request->input('contract_address')) {
            $cond['contract_address'] = $request->input('contract_address');  
        }
        $data = Order::where($cond)->orderby($order_by[0],$order_by[1])->paginate($page_size);


        $data->getCollection()->transform(function ($value) {
            $value->format();
            $value->item->format();
            return $value;
        });

        return $this->success($data);


    }

    private function cleanNumber($num){
        $explode = explode('.', $num);
        $count   = strlen(rtrim($explode[1],'0'));
        return bcmul("$num",'1', $count);
    }

    public function getBuySign(OrderRequest $request) {

        // echo "ok";
        // exit;
        //为了避免出问题先查询一次有多少个finish_time是0的订单
        $order_count = Order::where([
            'contract_address'  =>  $request->input('contract_address'),
            'token_id'          =>  $request->input('token_id'),
        ])->count();
        if ($order_count != 1) {
            return $this->failed('There is more than one corresponding order');
        }

        $order = Order::where([
            'contract_address'  =>  $request->input('contract_address'),
            'token_id'          =>  $request->input('token_id'),
        ])->first();
  
        if (!$order) {
            return $this->failed('order is not exist');
        }

        if ($order->expire_time < time()) {
            return $this->failed('order is expired');
        }


        Log::debug('查询到的order的价格是:'.$order->price);
        Log::debug('传入的价格是:'.$request->input('price'));
        Log::debug('对比结论是:'.bccomp($order->price,$request->input('price'),8) );

        if (bccomp($order->price,$request->input('price'),8) !== 0) {
            return $this->failed('order price was changed');
        }

        $seller = strtolower($order->from_address);
        $buyer = strtolower($request->input('address'));

        if ($buyer == $seller) {
            return $this->failed('buyer cannot be seller');
        }

        $signHelper = new EthSign();
        $deadline = time()+300;

        $fee = $this->cleanNumber(bcmul($order->price,0.01,18));

        $result = $signHelper->getOrderSignature(
            $order->contract_address,
            $order->token_id,
            $seller,
            $buyer,
            $order->price,
            $fee,
            $deadline 
        );

        if ($result) {
            $json_data = json_decode($result,true);
            return $this->success([
                'deadline'  => $deadline,
                'from'      => $seller,
                'to'        => $buyer,
                'contract_address'  =>  $order->contract_address,
                'token_id'  =>  $order->token_id,
                'value'     =>  $order->price,
                'fee'       =>  $fee,
                'r'         =>  $json_data['r'],
                's'         =>  $json_data['s'],
                'v'         =>  $json_data['v'],
            ]);
        }


    }




}
