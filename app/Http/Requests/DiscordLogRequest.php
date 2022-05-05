<?php

namespace App\Http\Requests;
use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Route;

use App\Rules\Language;
use App\Rules\ClubId;
// use App\Rules\UserId;
// use App\Rules\IdList;
// use App\Rules\TimeCond;
// use App\Rules\NumberCond;
// use App\Rules\UserType;
// use App\Rules\DateFormat;
// use App\Rules\AvatarId;

class DiscordLogRequest extends BaseRequest
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
                    'club_id'     => ['required','integer',new ClubId],
                    'action_type' => ['string'],
                    'order_by'    => ['string','in:create_time_desc,create_time_asc'],
                    'page'        => ['integer'],
                    'page_size'   => ['integer','min:1','max:50'],
                ];

            case 'listByCond':
                return [
                    'club_id'    => ['required','integer',new ClubId],
                    'order_by'    => ['string','in:create_time_desc,create_time_asc'],
                    'page'        => ['integer'],
                    'page_size'   => ['integer','min:1','max:50'],
                ];

            case 'load':
                return [
                    'club_id'   => ['required','integer',new ClubId],
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
