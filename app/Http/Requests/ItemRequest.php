<?php

namespace App\Http\Requests;
use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

use App\Rules\Language;
use App\Rules\AvatarId;
use App\Rules\EthContractAddress;
use App\Rules\EthAddress;

use App\Rules\Draft;

use Illuminate\Support\Facades\Log;

class ItemRequest extends BaseRequest
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
    public function rules():array
    {


        $action =  get_current_method_name();


        switch($action) {
            case 'add':
                return [
                    'contract_address' => ['required','string',new EthContractAddress],
                    'token_id'         => ['required','integer']
                ];

            case 'update':
                return [
                    'id'            => ['required','integer','exists:b_item,id,delete_time,NULL'],
                    'image_url'     => ['string','url'],
                ];

            case 'image':
                return [
                    'contract_address' => ['required','string',new EthContractAddress],
                    'token_id'         => ['required','integer'],
                    'width'            => ['required','integer','in:200,500']
                ];

            case 'list':
                return [
                    'page_size'   => ['integer','min:1','max:50'],
                    'has_order'   => ['boolean'],
                    'order_by'    => ['string','in:expire_time_asc,price_desc,price_asc,create_time_desc'],
                ];

            case 'myList':
                return [
                    'page_size'   => ['integer','min:1','max:50'],
                ];

            case 'load':
                return [
                    'id'                => ['required_without:contract_address','integer','exists:b_item,id,delete_time,NULL'],
                    'contract_address'  => ['required_without:id','string',new EthContractAddress],
                    'token_id'          => ['required_without:id','integer']
                ];


            default:
                break;
        }
    }


    public function messages():array
    {
        return [
        ];
    }


}
