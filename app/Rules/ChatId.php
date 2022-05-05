<?php

namespace App\Rules;

use App\Models\Club;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;

class ChatId implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {

        $chat_id_arr = explode('_',$value);
        if (count($chat_id_arr) !== 2)  {
            return false;
        }

        $lang = $chat_id_arr[1];

        if (auth('api')->user()->lang !== $lang) {
            Log::debug(sprintf('添加消息message被拒绝，原因是用户当前的语言是:%s,添加消息的语言是:%s',auth('api')->user()->lang,$lang));
            return false;
        }

        return true;

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The club_id is not allowed.';
    }
}
