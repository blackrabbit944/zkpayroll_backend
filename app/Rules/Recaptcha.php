<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Recaptcha implements Rule
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
        // if (app()->environment('local')) {
        //     return true;
        // }
        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

        Log::info('准备请求api去获得recaptcha是否被接受'.$verifyUrl);

        $response = Http::asForm()->post($verifyUrl, [
            'secret'    => config('recaptcha.server_key'),
            'response'  => $value,
        ]);

        Log::info('请求API的结果是:'.json_encode($response->json()));

        // 取得 response 的 success 值
        return $response->json()['success'];
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The recaptcha token is not allowed.';
    }
}
