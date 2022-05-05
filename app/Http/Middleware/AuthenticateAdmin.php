<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Facades\Log;

class AuthenticateAdmin
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {

        $user = $this->auth->guard($guard)->user();

        Log::info('进入验证的request是'.json_encode($request->header()));
        Log::info('进入验证的用户是'.json_encode($user));

        if (!$user) {
            return response('Unauthorized.', 401);
        }

        if ($user->is_super_admin != 1) {
            return response('Unauthorized Admin.', 401);
        }

        return $next($request);
    }
}
