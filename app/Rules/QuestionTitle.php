<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use App\Repositories\UploadImgRepository;

class QuestionTitle implements Rule
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

        // if (substr(rtrim($value), -1) != "?") { 
        //     // Do stuff
        // }

        if (strpos($value,'?')===false){
            if (strpos($value,"？")===false) {
                return false;
            }
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
        return '问题至少必须包含一个问号';
    }
}
