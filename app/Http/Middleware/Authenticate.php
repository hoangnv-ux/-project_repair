<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    public function handle(Request $request, Closure $next, $guard = 'user', $mode = 'protect')
    {
        $loginRoute = $guard === 'admin' ? 'admin.login' : 'user.login';
        $dashboardRoute = $guard === 'admin' ? 'admin.dashboard' : 'user.dashboard';
        $isloggedIn = Auth::guard($guard)->check();
        if($mode === 'protect' && !$isloggedIn){
            return redirect()->route($loginRoute);
        }
        if($mode === 'redirect' && $isloggedIn){
            return redirect()->route($dashboardRoute);
        }

        return $next($request);
    }
}
