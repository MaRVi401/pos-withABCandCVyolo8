<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    /**
     * Helper untuk mengambil role_id milik 'Customer' atau 'User'
     */
    private function getCustomerRoleId()
    {
        $role = Role::whereIn('role_name', ['Customer', 'customer', 'User', 'user'])->first();
        return $role ? $role->role_id : 2; // Default fallback ke ID 2
    }

    public function index(Request $request)
    {
        $customerRoleId = $this->getCustomerRoleId();

        $customers = User::where('role_id', $customerRoleId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $customers
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|max:100|unique:users,email',
            'password'  => 'required|string|min:6',
            'phone'     => 'nullable|string|max:20',
            'address'   => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $customerRoleId = $this->getCustomerRoleId();

        $customer = User::create([
            'role_id'       => $customerRoleId,
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'phone'         => $validated['phone'] ?? null,
            'address'       => $validated['address'] ?? null,
            'is_active'     => $validated['is_active'] ?? true,
            'created_at'    => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil ditambahkan!',
            'data'    => $customer
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $customer = User::where('user_id', $id)->firstOrFail();

        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|max:100|unique:users,email,' . $id . ',user_id',
            'password'  => 'nullable|string|min:6',
            'phone'     => 'nullable|string|max:20',
            'address'   => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $updateData = [
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
            'address'   => $validated['address'] ?? null,
            'is_active' => $validated['is_active'] ?? $customer->is_active
        ];

        // Update password hanya jika diisi
        if (!empty($validated['password'])) {
            $updateData['password_hash'] = Hash::make($validated['password']);
        }

        $customer->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Data pelanggan berhasil diperbarui!',
            'data'    => $customer
        ]);
    }

    public function toggleStatus($id)
    {
        $customer = User::where('user_id', $id)->firstOrFail();
        $customer->update(['is_active' => !$customer->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Status pelanggan berhasil diubah!',
            'data'    => $customer
        ]);
    }

    public function destroy($id)
    {
        $customer = User::where('user_id', $id)->firstOrFail();
        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data pelanggan berhasil dihapus!'
        ]);
    }
}