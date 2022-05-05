<?php

namespace App\Http\Requests;
use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Route;

use App\Rules\Language;
use App\Rules\AvatarId;
use App\Rules\Feeling;
use App\Rules\ClubId;
use App\Rules\UserId;
use App\Rules\IdList;
use App\Rules\TimeCond;
use App\Rules\NumberCond;
use App\Rules\UserType;
use App\Rules\DateFormat;

class ClubUserRequest extends BaseRequest
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
                    'is_join'     => ['boolean'],
                    'is_left'     => ['boolean'],
                    'kw'          => ['string'],
                    'order_by'    => ['string','in:create_time_desc,create_time_asc'],
                    'page'        => ['integer'],
                    'page_size'   => ['integer','min:1','max:50'],
                ];

            case 'listByCond':
                return [
                    'club_id'    => ['required','integer',new ClubId],

                    'ids'         => ['required_without_all:is_fav,is_join,create_time,reputation,user_type,club_id',new IdList],

                    'is_fav'      => ['boolean'],
                    'is_join'     => ['boolean'],
                    'create_time' => [new TimeCond],
                    'user_type'   => [new UserType],

                    'order_by'    => ['string','in:create_time_desc,create_time_asc,reputation_desc'],
                    'page'        => ['integer'],
                    'page_size'   => ['integer','min:1','max:50'],
                ];

            case 'load':
                return [
                    'club_id'   => ['required','integer',new ClubId],
                    'user_id'   => ['required','integer',new UserId],
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
