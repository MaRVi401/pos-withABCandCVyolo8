<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * API Login Utama (Admin & Kasir)
     * Hanya mengizinkan role_id = 1 (Admin/Kasir) untuk login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Cari user berdasarkan email beserta relasi role
        $user = User::with('role')->where('email', $request->email)->first();

        // Cek keberadaan user dan kecocokan password_hash
        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.'
            ], 401);
        }

        // Cek apakah akun aktif
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda dinonaktifkan. Silakan hubungi admin.'
            ], 403);
        }

        // BLOKIR CUSTOMER: Jika role_id = 2 (Customer), tolak login di portal admin
        if ((int)$user->role_id !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak! Akun Customer tidak diizinkan masuk ke Web Admin.'
            ], 403);
        }

        // Buat Sanctum Token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data'    => [
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => [
                    'user_id'   => $user->user_id,
                    'role_id'   => $user->role_id, // Disertakan agar frontend bisa cek role
                    'role_name' => $user->role->role_name ?? 'Admin',
                    'name'      => $user->name,
                    'email'     => $user->email,
                    'phone'     => $user->phone,
                    'address'   => $user->address,
                ]
            ]
        ], 200);
    }

    /**
     * API Register khusus Customer (Role ID = 2)
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:100',
            'email'    => 'required|string|email|max:100|unique:users,email',
            'password' => 'required|string|min:8',
            'phone'    => 'nullable|string|max:20',
            'address'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Simpan User Baru dengan Role ID 2 (Customer)
        $user = User::create([
            'role_id'       => 2, // Default Role: Customer
            'name'          => $request->name,
            'email'         => $request->email,
            'password_hash' => Hash::make($request->password),
            'phone'         => $request->phone,
            'address'       => $request->address,
            'is_active'     => true,
            'created_at'    => now(),
        ]);

        $user->load('role');

        // Buat Token Otomatis setelah Register
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi customer berhasil.',
            'data'    => [
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => [
                    'user_id'   => $user->user_id,
                    'role_id'   => $user->role_id,
                    'role_name' => $user->role->role_name ?? 'Customer',
                    'name'      => $user->name,
                    'email'     => $user->email,
                    'phone'     => $user->phone,
                    'address'   => $user->address,
                ]
            ]
        ], 201);
    }

    /**
     * API Logout (Revoke Token)
     */
    public function logout(Request $request)
    {
        // Hapus token yang sedang digunakan
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.'
        ], 200);
    }
}