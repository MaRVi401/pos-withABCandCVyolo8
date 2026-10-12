<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. Jika belum login
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // 2. Cek apakah role_id BUKAN Admin (misal Customer/Role lain)
        $isAdmin = (int)$user->role_id === 1 || ($user->role && strtolower($user->role->role_name) === 'admin');

        if (!$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak! Halaman ini khusus untuk Admin.'
            ], 403);
        }

        return $next($request);
    }
}