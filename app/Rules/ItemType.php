<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ItemType implements Rule
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

        $allow_types = [
            'question',
            'answer',
            'comment',
            'minblog',
            'share',
            'post'
        ];

        if (in_array($value, $allow_types)) {
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
        return 'The item_type is not allowed.';
    }
}
