<?php

namespace App\Http\Middleware;

use Closure;
use App;

class SetLocalization
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
        if ($request->language == 'en') {
            App::setLocale('en');
        }

        return $next($request);
    }
}
