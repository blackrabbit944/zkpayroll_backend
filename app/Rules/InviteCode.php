<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;

use App\Models\Invitation;

class InviteCode implements Rule
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

        Log::info('传入的code是'.$value);
        $invitation = Invitation::where([
            'code'    =>  strtoupper($value)
        ])->first();

        Log::info('传入code以后获得的是'.json_encode($invitation));

        if ($invitation && !$invitation->isUse()) {
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
        return 'The invite_code is not exist.';
    }
}
