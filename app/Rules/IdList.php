<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;


class IdList implements Rule
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

        $id_arr = explode(',', $value);

        foreach($id_arr as $id) {
            if (!is_numeric($id)) {
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
        return 'The id list must be numberic in each id.';
    }
}
