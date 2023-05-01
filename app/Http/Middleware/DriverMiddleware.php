<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DriverMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (Auth::user()['role_id'] == Company::IS_DRIVER || Auth::user()['role_id'] == Company::IS_OWNER_AND_DRIVER){
            return $next($request);
        }
        return response()->json(['success' => false, 'message' => 'Permission denied'], 403);
    }
}
