<?php

namespace App\Http\Requests;
use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

use App\Rules\Language;

use App\Rules\EthAddress;
use App\Rules\SalaryContractAddress;

use Illuminate\Support\Facades\Log;

class SalaryRequest extends BaseRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        //暂时让所有的验证都通过
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {

        $action =  get_current_method_name();

        switch($action) {
            case 'add':
                return [
                    'contract_address'  => ['required','string',new SalaryContractAddress],
                    'address'           => ['required','string',new EthAddress],
                    'amount'            => ['required','numeric'],
                    'name'              => ['required','string'],
                ];

            case 'update':
                return [
                    'id'                => ['required','integer','exists:b_salary,id,delete_time,NULL'],
                    'contract_address'  => ['string',new SalaryContractAddress],
                    'address'           => ['string',new EthAddress],
                    'amount'            => ['numeric','max:10000000'],
                    'name'              => ['string'],
                ];

            case 'list':
                return [
                    'keyword'     => ['string'],
                    'page_size'   => ['integer','min:1','max:50'],
                ];

            case 'load':
                return [
                    'id'                => ['required','integer','exists:b_salary,id,delete_time,NULL'],
                ];


            case 'delete':
                return [
                    'id'                => ['required','integer','exists:b_salary,id,delete_time,NULL'],
                ];

            default:
                break;
        }
    }


    public function messages(): array
    {
        return [
        ];
    }


}
