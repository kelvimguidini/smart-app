<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateApi
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        if (!Auth::guard('api')->check()) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        return $next($request);
    }
}
