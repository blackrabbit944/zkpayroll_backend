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

class UserAnalyticsRequest extends BaseRequest
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
        $club_id = $this->input('club_id');


        switch($action) {
            case 'set':
                return [
                    'invite_address' => ['required','string',new EthContractAddress],
                ];

            case 'list':
                return [
                    'page_size'   => ['integer','min:1','max:50'],
                ];

            case 'load':
            case 'whitelistData':
                return [];

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
