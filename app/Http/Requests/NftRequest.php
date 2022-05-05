<?php

namespace App\Http\Requests;
use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

// use App\Rules\Language;
// use App\Rules\EthAddress;
// use App\Rules\ExpireTime;
// use App\Rules\EthContractAddress;
// use App\Rules\Ens;

use Illuminate\Support\Facades\Log;

class NftRequest extends BaseRequest
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
            case 'image':
                return [
                    'name'          => ['string','max:32'],
                    'unique_name'   => ['string','alpha_dash'],
                    'type'          => ['required'],
                    'font'          => ['required','numeric','between:1,15']
                ];

            default:
                break;


        }
    }


    public function messages(): array
    {
        return [
            'unique_name.alpha_dash'    =>  'Numbers, letters and underscores only'
        ];
    }


}