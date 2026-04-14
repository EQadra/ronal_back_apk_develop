<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckSingleSession
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $incomingToken = $request->bearerToken();

            if (!$user || $user->current_token !== $incomingToken) {
                return response()->json([
                    'message' => 'Sesión inválida o cerrada en otro dispositivo.'
                ], 401);
            }

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Token inválido o expirado'
            ], 401);
        }

        return $next($request);
    }
}