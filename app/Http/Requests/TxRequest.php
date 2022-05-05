<?php

namespace App\Http\Requests;
use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Route;

use App\Rules\ClubId;

class TxRequest extends BaseRequest
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


            case 'list':
                return [
                    'club_id'     => ['required_without:contract_address','integer','exists:b_club,id'],
                    'contract_address'  =>  ['required_without:club_id','string'],
                    'tx_type'     => ['string','in:mint,transfer'],
                    'order_by'    => ['string','in:action_time_desc,action_time_asc'],
                    'page'        => ['integer'],
                    'page_size'   => ['integer','min:1','max:50'],
                ];

            case 'load':
                return [
                    'id'   => ['required','integer','exists:b_tx,id'],
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
