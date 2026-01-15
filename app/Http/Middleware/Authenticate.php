<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class Authenticate
{
    public function handle(Request $request, Closure $next, $guard = 'user')
    {
        $cookieName = $guard === 'admin' ? 'admin_token' : 'user_token';
        $loginRoute = $guard === 'admin' ? 'admin.login' : 'user.login';

        // For API: get token from header
        $token = $request->bearerToken();

        // For Blade: get token from cookie
        if (!$token) {
            $token = $request->cookie($cookieName);
        }

        if (!$token) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            return redirect()->route($loginRoute);
        }

        try {
            JWTAuth::setToken($token);
            $user = JWTAuth::setRequest($request)->authenticate();

            if (!$user || !($user instanceof \App\Models\Admin) && $guard === 'admin') {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
                return redirect()->route($loginRoute);
            }

            if (!$user || !($user instanceof \App\Models\User) && $guard === 'user') {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
                return redirect()->route($loginRoute);
            }

            auth($guard)->setUser($user);
        } catch (JWTException $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Token invalid'], 401);
            }
            return redirect()->route($loginRoute);
        }

        return $next($request);
    }
}
