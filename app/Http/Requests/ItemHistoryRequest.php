<?php

namespace App\Http\Requests;
use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

use App\Rules\Language;
use App\Rules\AvatarId;
use App\Rules\CategoryId;
use App\Rules\ClubName;
use App\Rules\EthContractAddress;

use App\Rules\Draft;

use Illuminate\Support\Facades\Log;

class ItemHistoryRequest extends BaseRequest
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
                    'contract_address' => ['required_without:action_name','string',new EthContractAddress],
                    'token_id'         => ['required_without:action_name','integer'],
                    'action_name'      => ['required_without:contract_address','string','in:sale'],
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
