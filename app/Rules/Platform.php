<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;


class Platform implements Rule
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
        $platforms = [
            'instagram',
            'twitter',
            'tiktok',
            'youtube',
            'github',
            'linkedin',
            'wechat',
            'whatsapp',
            'email'
        ];

        if (in_array($value,$platforms)) {
            return true;
        }else {
            return false;
        }

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The platform is not allowed.';
    }
}
