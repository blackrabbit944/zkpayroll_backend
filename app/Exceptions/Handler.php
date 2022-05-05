<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Anik\Form\ValidationException as AnikValidationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\Log;

use App\Exceptions\ProgramException;
use App\Exceptions\ApiException;

use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        AnikValidationException::class,
    ];



    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        ///表单验证错误的话用json输出
        if ($exception instanceof AnikValidationException) {
            return response()->json([
                'code'     => $exception->getCode(),
                'status'   => 'error',
                'messages' => $exception->getResponse()
            ], $exception->getCode());
        } else if ($exception instanceof ThrottleRequestsException) {
            // $header = request()->header();
            // $true_ip = $header['cf-connecting-ip'][0] ?? '';
            // if (!$true_ip) {
            //     $true_ip = request()->ip(); 
            //     Log::info("cf-connecting-ip 是空的，将使用 request()->ip() 作为 IP 限制器");
            // }

            // Log::debug("客户端访问请求过多，被限制了 (draw 接口）", [$true_ip]);
            
            // if (request()->expectsJson()) {
            //     return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 200);
            // }
        }

        return parent::render($request, $exception);


    }
}
