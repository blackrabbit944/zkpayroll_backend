<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class SalaryContractAddress implements Rule
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

        $allow_list = [
            '0xed24fc36d5ee211ea25a80239fb8c4cfd80f12ee'    =>  'busd',
            '0x822ca080e094bf068090554a19bc3d6618c800b3'    =>  'usdt'
        ];

        if (!$value) {
            return false;
        }

        if (strlen($value) != 42) {
            return false;
        }

        $v = strtolower($value);
        if (isset($allow_list[$v])) {
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
        return 'The contract is not allowed.';
    }
}
