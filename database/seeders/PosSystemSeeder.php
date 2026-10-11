<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PosSystemSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Roles
        DB::table('roles')->insert([
            ['role_id' => 1, 'role_name' => 'Admin'],
            ['role_id' => 2, 'role_name' => 'Customer'],
        ]);

        // 2. Seed Default Admin User
        DB::table('users')->insert([[
            'role_id' => 1,
            'name' => 'Ahmad Yassin (Admin)',
            'email' => 'admin@pos.com',
            'password_hash' => Hash::make('12345678'),
            'phone' => '081234567890',
            'address' => 'Subang, Jawa Barat',
            'is_active' => true,
            'created_at' => now(),
        ], [
            'user_id' => 2,
            'role_id' => 2,
            'name' => 'Yss Customer',
            'email' => 'customer@pos.com',
            'password_hash' => Hash::make('12345678'),
            'phone' => '081234567890',
            'address' => 'Subang, Jawa Barat',
            'is_active' => true,
            'created_at' => now(),
        ]]);

        // 3. Seed Payment Methods
        DB::table('payment_methods')->insert([
            ['payment_method_id' => 1, 'method_name' => 'Cash'],
            ['payment_method_id' => 2, 'method_name' => 'QRIS'],
            ['payment_method_id' => 3, 'method_name' => 'Transfer Bank'],
            ['payment_method_id' => 4, 'method_name' => 'Credit / Debit Card'],
        ]);

        // 4. Seed Categories
        DB::table('categories')->insert([
            ['category_id' => 1, 'category_name' => 'Minuman', 'description' => 'Aneka kopi dan olahan minuman segar'],
            ['category_id' => 2, 'category_name' => 'Makanan / Makanan Ringan', 'description' => 'Snack dan makanan berat'],
            ['category_id' => 3, 'category_name' => 'Merchandise', 'description' => 'Peralatan dan souvenir toko'],
        ]);

        // 5. Seed Products (Fast-moving & Slow-moving)
        DB::table('products')->insert([
            [
                'product_id' => 1,
                'category_id' => 1,
                'sku' => 'KPG-AR-250',
                'product_name' => 'Kopi Susu Gula Aren 250ml',
                'buy_price' => 12000.00,
                'sell_price' => 18000.00,
                'stock_quantity' => 100,
                'min_stock_alert' => 10,
                'created_at' => now(),
            ],
            [
                'product_id' => 2,
                'category_id' => 3,
                'sku' => 'MUG-KC-001',
                'product_name' => 'Cangkir Kaca Premium',
                'buy_price' => 15000.00,
                'sell_price' => 25000.00,
                'stock_quantity' => 12,
                'min_stock_alert' => 5,
                'created_at' => now(),
            ],
            [
                'product_id' => 3,
                'category_id' => 2,
                'sku' => 'RTB-CK-001',
                'product_name' => 'Roti Bakar Cokelat',
                'buy_price' => 10000.00,
                'sell_price' => 15000.00,
                'stock_quantity' => 40,
                'min_stock_alert' => 5,
                'created_at' => now(),
            ],
            [
                'product_id' => 4,
                'category_id' => 3,
                'sku' => 'TMB-SS-500',
                'product_name' => 'Tumbler Stainless Steel 500ml',
                'buy_price' => 27000.00,
                'sell_price' => 45000.00,
                'stock_quantity' => 8,
                'min_stock_alert' => 3,
                'created_at' => now(),
            ],
        ]);
    }
}
