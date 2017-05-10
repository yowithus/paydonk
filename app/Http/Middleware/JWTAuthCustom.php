<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class JWTAuthCustom
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
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'status'    => 0,
                    'message'   => 'user_not_found'
                ]);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'status'    => 0,
                'message'   => 'token_expired'
            ]);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'status'    => 0,
                'message'   => 'token_invalid'
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status'    => 0,
                'message'   => 'token_absent'
            ]);
        }

        return $next($request);
    }
}
