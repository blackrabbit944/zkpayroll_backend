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

use App\Rules\Ens;

use Illuminate\Support\Facades\Log;

class ProfileRequest extends BaseRequest
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
            case 'set':
                return [
                    'bio'                   => ['string','max:256','min:0'],
                    'name'                  => ['string','max:64','min:2'],
                    'theme'                 => ['string','max:32','min:2'],
                    'ens'                   => ['string',new Ens],
                    'unique_name'           => ['string','alpha_dash'],
                ];

            case 'load':
                return [
                    'user_id'     => ['required_without:name','integer','exists:b_user,user_id'],
                    'name'        => ['required_without:user_id','string','alpha_dash'],

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