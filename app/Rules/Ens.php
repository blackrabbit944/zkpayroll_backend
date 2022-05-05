<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;


class Ens implements Rule
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
        $arr = explode('.',$value);

        if (count($arr) == 2 && $arr[1] == 'eth') {
            return true;
        }

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The ens is not allowed.';
    }
}
