<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class BotActionArray implements Rule
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
        if (!$value) {
            return false;
        }

        $allows = ['daily_price','whale_action','new_post'];
        $arr = explode(',',$value);
        foreach($arr as $a) {
            if (!in_array($a,$allows)) {
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
        return 'The BotAction is not correct.';
    }
}
