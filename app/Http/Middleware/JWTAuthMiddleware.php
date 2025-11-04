<?php

namespace App\Http\Middleware;

use App\Services\JWTService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JWTAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    protected $jwtService;

    public function __construct(JWTService $jwtservice){
        $this->jwtService = $jwtservice;
    }


    public function handle(Request $request, Closure $next): Response
    {
        //Obtaining the token of the header of Authorization
        $authHeader = $request->header('Authorization');

        if(!$authHeader || !str_starts_with($authHeader, 'Bearer')){
            return response()->json([
                'status' => 'not_authenticated',
                'message' => 'Missing or invalid Authorization header'
            ], 401);
        }

        //Extract token and remove the 'Bearer ' part
        $token = substr($authHeader, strlen('Bearer '));

        try {
            //decode the token using the JWT service
            $decoded = $this->jwtService->decodeAccessToken($token);

            if($decoded->type !== 'access_token'){
                return response()->json([
                    'status' => 'unauthorized',
                    'message' => 'Invalid token type'
                ],401);
            }

            // Add the user information to the request
            $request->merge([
                'auth_user_id' => $decoded->user_id ?? null,
                'auth_role' => $decoded->auth_role ?? null,
                'auth_email' => $decoded->email ?? null
            ]);

            // If the token is valid, we allow the request to proceed
            return $next($request);
        } catch (\Exception $error) {
            return response()->json([
                'status' => 'unauthorized',
                'message' => 'Invalid or expired token: ' . $error->getMessage()
            ], 401);
        }


    }
}
