<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class passMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        
        if($request->header('Authorization') != null )

        return $next($request);
    }
}
