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

class CollectionRequest extends BaseRequest
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
            case 'add':
                return [
                    'contract_address' => ['required','string',new EthContractAddress],
                ];

            case 'update':
                return [
                    'id'            => ['required','integer','exists:b_collection,id,delete_time,NULL'],
                    'name'          => ['string','min:1','max:64'],
                    'avatar_img_id' => ['integer',new AvatarId],
                    'cover_img_id'  => ['integer','exists:b_upload_img,img_id,file_type,cover'],
                    'is_verify'     => ['boolean'],
                    'chain'         => ['string','in:eth'],
                    'eip_type'      => ['string','in:erc721'],
                    'symbol'        => ['string','min:1','max:32'],
                    'item_count'    => ['integer'],
                    'discord_link'  => ['string','url'],
                    'twitter_link'  => ['string','url'],
                    'website_link'  => ['string','url'],
                    'instagram_link'=> ['string','url'],
                ];


            case 'list':
                return [
                    'keyword'     => ['string'],
                    'page_size'   => ['integer','min:1','max:50'],
                ];

            case 'load':
                return [
                    'contract_address' => ['required','string',new EthContractAddress]
                ];

            case 'hotList':
                return [
                    'page_size'   => ['integer','min:1','max:50'],
                ];
                
            case 'delete':
                return [
                    'contract_address' => ['required','string',new EthContractAddress],
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
