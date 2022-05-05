<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use App\Models\Club;

class ClubId implements Rule
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

        $club = Club::find($value);

        if ($club) {
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
        return 'The club_id is not exist.';
    }
}
