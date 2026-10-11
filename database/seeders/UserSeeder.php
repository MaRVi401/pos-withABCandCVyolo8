<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Matikan Foreign Key Checks sementara agar bisa truncate tabel yang berelasi
        Schema::disableForeignKeyConstraints();
        
        DB::table('users')->truncate();
        DB::table('roles')->truncate();

        Schema::enableForeignKeyConstraints();

        // 1. Seed Roles
        DB::table('roles')->insert([
            ['role_id' => 1, 'role_name' => 'Admin'],
            ['role_id' => 2, 'role_name' => 'Customer'],
        ]);

        // 2. Seed Default Users
        DB::table('users')->insert([
            [
                'role_id' => 1,
                'name' => 'Ahmad Yassin (Admin)',
                'email' => 'admin@pos.com',
                'password_hash' => Hash::make('12345678'),
                'phone' => '081234567890',
                'address' => 'Subang, Jawa Barat',
                'is_active' => true,
                'created_at' => now(),
            ],
            [
                'role_id' => 2,
                'name' => 'Yss Customer',
                'email' => 'customer@pos.com',
                'password_hash' => Hash::make('12345678'),
                'phone' => '081234567890',
                'address' => 'Subang, Jawa Barat',
                'is_active' => true,
                'created_at' => now(),
            ]
        ]);
    }
}