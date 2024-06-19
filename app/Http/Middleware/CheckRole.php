<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (Auth::user() && (Auth::user()->role == $role || Auth::user()->role == 'admin')) {
            return $next($request);
        }
        
        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
