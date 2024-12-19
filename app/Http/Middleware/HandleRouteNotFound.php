<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class HandleRouteNotFound
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($response->status() === 404 && $request->is('api/*')) {
            return Response::json(NULL, 404);
        }

        return $response;
    }
}
