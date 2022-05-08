<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Salary;

use App\Helpers\Salary as SalaryHelper;

use App\Http\Requests\SalaryRequest;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Gate;

use Carbon\Carbon;


class SalaryController extends Controller
{

    public function add(SalaryRequest $request) {

        $attributes = $request->only([
            'contract_address',
            'address',
            'amount',
            'name'
        ]);

        if ($request->user()->cannot('create', [Salary::class,$attributes])) {
            return $this->failed('you have no access for add Salary');
        }

        $salary = Salary::withTrashed()->where([
            'user_id'     =>  auth('api')->user()->user_id,
            'address'     =>  $request->input('address'),
            'contract_address'  =>  $request->input('contract_address'),
        ])->first();

        
        if (!$salary) {
            $salary = Salary::create($attributes);
        }elseif ($salary->trashed()) {
            $salary->restore();
            $salary->fill($attributes);
            $salary->save();
        }

        return $this->success($salary);
    }


    public function delete(SalaryRequest $request) {

        $salary = Salary::where([
            'id'     =>  $request->input('id'),
        ])->first();

        if (!$salary) {
            return $this->failed('salary is not exist');
        }

        if ($request->user()->cannot('delete', $salary)) {
            return $this->failed('you have no access for delete Salary');
        }

        $ret = $salary->delete();

        return $this->success([]);
    }

    public function update(SalaryRequest $request) {

        $attributes = $request->only([
            'contract_address',
            'address',
            'amount',
            'name'
        ]);

        $salary = Salary::find($request->input('id'));

        if (!$salary) {
            return $this->failed('salary is not exist');
        }

        if ($request->user()->cannot('update', $salary)) {
            return $this->failed('you have no access for update salary');
        }

        $salary->fill($attributes);
        $salary->save();

        $salary->fresh();
        $salary->format();

        return $this->success($salary);
    }



    public function load(SalaryRequest $request) {

        $salary = Salary::where([
            'id'     =>  $request->input('id'),
        ])->first();

        if (!$salary) {
            return $this->failed('Salary is not exist');
        }

        $salary->format();

        return $this->success($salary);
    }


    public function list(SalaryRequest $request) {

        // $page_size = get_page_size($request);
        $order = get_order_by($request,'id_desc');

        $cond['user_id'] = auth('api')->user()->user_id;

        $data = Salary::where($cond)->orderby($order[0],$order[1])->get();

        $data->transform(function ($value) {
            $value->format();
            return $value;
        });

        return $this->success($data);

    }




}
