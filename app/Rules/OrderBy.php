<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;


class OrderBy implements Rule
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
            'rank_desc',
            'rank_asc',
            'create_time_desc',
            'create_time_asc',
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
        return 'The order_by is not allowed.';
    }
}
