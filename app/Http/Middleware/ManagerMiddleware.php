<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManagerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        //The user need to pass through the jwt auth middleware first
        if(!$request->has('auth_role')){
            return response()->json([
                'success' => 'false',
                'message' => 'You do not have permission to access this resource'
            ], 401);
        }

        if($request->input('auth_role') !== 'manager'){
            return response()->json([
                'success' => 'false',
                'message' => 'You do not have permission to access this resource'
            ], 403);
        }

        return $next($request);
    }
}
