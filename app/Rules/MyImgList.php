<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use App\Models\UploadImg;

class MyImgList implements Rule
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

        $img_ids = explode(',',$value);

        $ret = true;
        foreach($img_ids  as $img_id) {
            $img = UploadImg::find($img_id);
            if (!$img || !($img->user_id == auth('api')->user()->user_id)) {
                $ret = false;
            }
        }

        return $ret;    
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The image_ids is not exist.';
    }
}
