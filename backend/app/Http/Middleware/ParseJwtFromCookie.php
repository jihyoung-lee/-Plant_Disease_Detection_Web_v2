<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ParseJwtFromCookie
{
    public function handle($request, Closure $next)
    {
        if ($request->hasCookie('access_token')) {
            $token = $request->cookie('access_token');
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        return $next($request);
    }
}
