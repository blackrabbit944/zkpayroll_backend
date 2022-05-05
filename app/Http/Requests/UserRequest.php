<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Log;

// use App\Rules\AvatarId;
use App\Rules\InviteCode;
use App\Rules\RecaptchaV3;
use App\Rules\AvatarId;
use App\Rules\Language;
use App\Rules\EthContractAddress;

class UserRequest extends BaseRequest
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

        // if (app()->environment(['local','testing'])) {
        //     $recaptcha_token_rule_register = ['string'];
        //     $recaptcha_token_rule_login    = ['string'];
        // }else {
        //     $recaptcha_token_rule_register = ['required','string',new RecaptchaV3('register')];
        //     $recaptcha_token_rule_login = ['required','string',new RecaptchaV3('register')];
        // }

        switch($action) {

            case 'login':
                return [
                    'address'   =>  ['required','string'],
                    'params'    =>  ['required','string'],
                    'sign'      =>  ['required','string'],
                    'source_from'   =>  ['integer']
                ];

            case 'fakeLogin':
                return [
                    'address'   =>  ['required','string'],
                ];

            case 'logout': 
                return [];

            case 'load':
                return [
                    'user_id' => ['required','integer','exists:b_user,user_id']
                ];

            default:
                break;
        }


    }


    public function messages(): array
    {
        return [
            'recaptcha_token.string' => 'must pass the recaptcha verification.',
        ];
    }



}
