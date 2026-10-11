<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        // Pastikan user terautentikasi dan memiliki role_id = 1 (Admin)
        if (!$request->user() || (int) $request->user()->role_id !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Halaman ini khusus untuk Admin.'
            ], 403); // Status 403 Forbidden
        }

        return $next($request);
    }
}