<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //We get the header of the request
        $apikey = $request->header('X-API-Key');

        //Then we get the api key from the .env file
        $validApiKey = config('app.api_key');

        //Validation
        if(!$apikey || $apikey !== $validApiKey){
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'invalid or missing API key'
            ], 401);
        }

        return $next($request);
    }
}
