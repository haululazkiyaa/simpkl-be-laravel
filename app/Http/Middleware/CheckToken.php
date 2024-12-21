<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');

        $token = $authHeader && str_starts_with($authHeader, 'Bearer ')
            ? substr($authHeader, 7)
            : null;

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Token is missing.'
            ], 401);
        }

        try {
            $decoded = JWT::decode($token, new Key(config('app.ACCESS_TOKEN_SECRET'), 'HS256'));

            $request->merge([
                'userId'   => $decoded->userId ?? null,
                'usernameUser' => $decoded->username ?? null,
                'role'     => $decoded->role ?? null,
            ]);

            return $next($request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Invalid or expired token.',
                'error'   => $e->getMessage()
            ], 403);
        }
    }
}
