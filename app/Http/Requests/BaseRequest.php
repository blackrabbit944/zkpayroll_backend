<?php

namespace App\Http\Requests;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Anik\Form\FormRequest;

use Illuminate\Support\Facades\Log;

class BaseRequest extends FormRequest
{

    protected function failedValidation(Validator $validator)
    {
        $error= $validator->errors()->all();
        throw new HttpResponseException($this->failJson(400, $error));
    }

    protected function errorResponse() : JsonResponse
    {
        // return response()->json([
        //     'message' => $this->errorMessage(),
        //     'errors' => $this->validator->errors()->messages(),
        // ], $this->statusCode());
        
        // Log::debug('调用到这里的报错');

        return response()->json(
            [
                'code'     => $this->statusCode(),
                'status'   => 'error',
                'messages' => $this->validator->errors()->messages(),
            ],
            $this->statusCode()
        );
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        //暂时让所有的验证都通过
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {      
        return [
        ];
    }
}
