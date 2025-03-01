<?php

namespace App\Http\Middleware;
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;



class CorsMiddleware
{
    
    public function handle($request, Closure $next)
    {
        $headers = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];

        if ($request->isMethod('OPTIONS')) {
            return response()->json('OK', 200)->withHeaders($headers);
        }

        $response = $next($request);

        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}
