<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class JWTAuth extends \Tymon\JWTAuth\Middleware\GetUserFromToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! $token = $this->auth->setRequest($request)->getToken()) {
            return response()->json([
                'status'    => 0,
                'message'   => 'token_not_provided'
            ]);
        }

        try {
            $user = $this->auth->authenticate($token);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'status'    => 0,
                'message'   => 'token_expired'
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status'    => 0,
                'message'   => 'token_invalid'
            ]);
        }
        
        if (! $user) {
            return response()->json([
                'status'    => 0,
                'message'   => 'user_not_found'
            ]);
        }

        return $next($request);
    }
}
