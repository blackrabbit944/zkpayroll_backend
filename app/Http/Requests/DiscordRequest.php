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

class DiscordRequest extends BaseRequest
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

            case 'userCallback':
                return [
                    'state' => ['required','string'],
                    'code'  => ['required','string']
                ];

            case 'guildCallback':
                return [
                    'state' => ['required','string'],
                    'code'  => ['required','string'],
                    'guild_id'      =>  ['required','string'],
                    'permissions'   =>  ['required','string'],
                ];

            case 'getClubInfo':
            case 'verifyNft':
                return [
                    'club_id'   => ['required','integer','exists:b_club,id,delete_time,NULL'],
                ];

            case 'getUserOwnGuilds':
            case 'test':
                return [];


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
