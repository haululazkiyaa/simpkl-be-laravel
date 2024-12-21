<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class CheckUserRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $userRole = $request->role ?? null;

        if (!$userRole || !in_array($userRole, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Access denied due to insufficient role.'
            ], 403);
        }

        return $next($request);
    }
}
