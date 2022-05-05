<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Route;

use App\Rules\Draft;
use App\Rules\ClubId;
use App\Rules\IdList;
use App\Rules\TagList;
use App\Rules\UserType;

class PostRequest extends BaseRequest
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

        //,new Draft
        switch($action) {
            case 'add':
                return [
                    'content'       => ['string',new Draft],
                    'title'         => ['required','string','min:1','max:128'],
                    'club_id'       => ['required','integer',new ClubId],
                ];

            case 'update':
                return [
                    'post_id'   => ['required','integer','exists:b_post,id,delete_time,NULL'],
                    'content'       => ['string',new Draft],
                    'title'         => ['string','min:1','max:128'],
                ];

            case 'list':
                return [
                    'club_id'     => ['integer',new ClubId],
                    'kw'          => ['string'],
                    'user_id'     => ['integer','exists:b_user,user_id'],
                    'page'        => ['integer','min:1'],
                    'page_size'   => ['integer','min:1','max:50'],
                    'zero_answer' => ['boolean'],
                ];

            case 'load':
                return [
                    'post_id' => ['required','integer','exists:b_post,id,delete_time,NULL']
                ];

            case 'delete':
                return [
                    'post_id' => ['required','integer','exists:b_post,id,delete_time,NULL']
                ];

            case 'search':
                return [
                    'kw' => ['string','min:1','max:128']
                ];

            case 'listByCond':
                return [
                    'club_id'    => ['required','integer',new ClubId],

                    'ids'         => ['required_without_all:tags,kw,user_type,in_user_ids',new IdList],

                    'tags'        => [new TagList],
                    'kw'          => ['string'],
                    'user_type'   => [new UserType],
                    'in_user_ids' => [new IdList],

                    'order_by'    => ['string','in:rank_desc,rank_asc,create_time_desc,create_time_asc'],

                    'page'        => ['integer'],
                    'page_size'   => ['integer','min:1','max:50'],
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
