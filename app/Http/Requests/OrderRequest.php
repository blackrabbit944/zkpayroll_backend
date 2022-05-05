<?php

namespace App\Http\Requests;
use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

use App\Rules\Language;
use App\Rules\AvatarId;
use App\Rules\EthAddress;
use App\Rules\ExpireTime;
use App\Rules\EthContractAddress;

use App\Rules\Draft;

use Illuminate\Support\Facades\Log;

class OrderRequest extends BaseRequest
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
            case 'delete':
            case 'update':
                return [
                    // 'contract_address' => ['required','string',new EthContractAddress],
                    // 'token_id'         => ['required','integer'],
                    // 'price'            => ['required','numeric','gt:0'],
                    // 'expire_time'      => ['required','integer',new ExpireTime],
                    'address'          => ['required','string',new EthAddress],
                    'params'           => ['required','string'],
                    'sign'             => ['required','string']
                ];

            case 'getBuySign':
                return [
                    'contract_address'  => ['required','string',new EthContractAddress],
                    'token_id'          => ['required','string'],
                    'price'             => ['required','string'],
                    'address'           => ['required','string',new EthAddress]
                ];

            case 'deleteByOwner':
                return [
                    'contract_address' => ['required','string',new EthContractAddress],
                    'token_id'         => ['required','string'],
                ];

            case 'list':
                return [
                    'contract_address'  => ['required_with:token_id','string',new EthContractAddress],
                    'token_id'          => ['integer'],
                ];

            case 'load':
                return [
                    'contract_address' => ['required_without:id','string',new EthContractAddress],
                    'token_id'         => ['required_without:id','string'],
                    'id'               => ['required_without:contract_address','integer'],
                ];
                
            case 'validateOwner':
                return [
                    'contract_address' => ['required_without:id','string',new EthContractAddress],
                    'token_id'         => ['required_without:id','string'],
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
