<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class BotUrl implements Rule
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

        $telegram_schema = '';
        $discord_schema = 'https://discord.com/api/webhooks/';

        $v = strtolower($value);
        if (stripos($v,$discord_schema) !== 0) {
            return false;
        }else {
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
        return 'The BotUrl is not correct.';
    }
}
