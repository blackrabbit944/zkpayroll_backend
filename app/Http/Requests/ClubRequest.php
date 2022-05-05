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

class ClubRequest extends BaseRequest
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
            case 'add':
                return [
                    'introduction'   => ['required','string','max:1024','min:0'],
                    'name'  => ['required','string','max:64','min:2'],
                    'unique_name'   => ['required','string','alpha_dash','unique:b_club,unique_name'],
                    'avatar_img_id' => ['required','exists:b_upload_img,img_id,delete_time,NULL'],

                    'name_in_nft'   =>   ['required','string','max:16','min:2'],
                    'nft_bg'        =>   ['required','string','in:orange,matrix,default,bg1,bg2,bg3'],
                    'nft_font'      =>   ['required','integer','min:1','max:13'],
                ];

            case 'update':
                return [
                    'id'            => ['required','integer','exists:b_club,id,delete_time,NULL'],
                    'introduction'           => ['string','max:1024','min:0'],
                    'name'          => ['string','max:64','min:2'],
                    'unique_name'   => ['string','alpha_dash'],
                    'avatar_img_id' => ['exists:b_upload_img,img_id,delete_time,NULL'],
                    'passcard_max_count'    =>  ['integer','max:20000','min:1'],
                    'passcard_type'    =>  ['string','max:32','min:1'],
                ];

            case 'delete':
                return [
                    'id'            => ['required','integer','exists:b_club,id,delete_time,NULL'],
                ];

            case 'list':
                return [
                    'is_mine'     => ['required_without:address','bool'],
                    'address'     => ['required_without:is_mine','string',new EthAddress],
                    'show_type'   => ['string','in:icon,text,button'],
                    'order_by'    => ['string','in:expire_time_asc,price_desc,price_asc,create_time_desc'],
                ];

            case 'load':
                return [
                    'id'    => ['required_without:name','integer','exists:b_club,id,delete_time,NULL'],
                    'name'  => ['required_without:id','string'],

                ];
            case 'getInviteLink':
                return [
                    'id'    => ['required_without:name','integer','exists:b_club,id,delete_time,NULL'],
                    'name'  => ['required_without:id','string'],
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