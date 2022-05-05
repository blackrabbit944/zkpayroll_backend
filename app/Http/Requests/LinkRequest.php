<?php

namespace App\Http\Requests;
use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

use App\Rules\Platform;
use App\Rules\EthAddress;

use App\Rules\IdList;

use Illuminate\Support\Facades\Log;

class LinkRequest extends BaseRequest
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
                    'text'      => ['required_unless:show_type,icon','string','between:2,32'],
                    'url'       => ['required_without_all:account','string','active_url'],
                    'account'   => ['required_without_all:url','string'],
                    'platform'  => ['required_without:text'],
                    'show_type' => ['required','string','in:icon,text,button'],
                ];

            case 'update':
                return [
                    'id'            => ['required','integer','exists:b_link,id,delete_time,NULL'],
                    'text'      => ['string','between:2,32'],
                    'platform'  => ['string',new Platform],
                    'account'  => ['string','between:1,64'],
                    'url'       => ['string','active_url'],
                    'show_type' => ['string','in:icon,text,button'],
                    'is_visible' => ['bool'],
                    'order_id'  => ['integer']
                ];

            case 'delete':
                return [
                    'id'            => ['required','integer','exists:b_link,id,delete_time,NULL'],
                ];

            case 'list':
                return [
                    'address'     => ['required','string',new EthAddress],
                    'show_type'   => ['string','in:icon,text,button'],
                    'order_by'    => ['string','in:expire_time_asc,price_desc,price_asc,create_time_desc'],
                ];

            case 'allList':
                return [
                    'address'     => ['required_without:name','string',new EthAddress],
                    'name'        => ['required_without:address','string','alpha_dash'],
                ];

            case 'load':
                return [
                    'id'            => ['required','integer','exists:b_link,id,delete_time,NULL'],
                ];

            case 'sort':
                return [
                    'ids'            => ['required',new IdList],
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
