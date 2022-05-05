<?php

namespace App\Exceptions;

use Exception;
use Throwable;
use Illuminate\Http\JsonResponse;

class ProgramException extends Exception
{

    protected $message;

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->message = $message;
    }

    public function report()
    {
        return true;
    }

    public function render()
    {
        return $this->json(400,[$this->getMessage()]);
    }

    protected function json(int $code, array $errors) : JsonResponse
    {
        return response()->json(
            [
                'code'     => $code,
                'status'   => 'error',
                'messages' => $errors,
            ]
        );
    }


}
