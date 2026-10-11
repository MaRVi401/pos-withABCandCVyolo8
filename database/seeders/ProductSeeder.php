<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::table('products')->truncate();

        Schema::enableForeignKeyConstraints();

        DB::table('products')->insert([
            // FAST MOVING (Stok >= 30)
            [
                'product_id' => 1,
                'category_id' => 1,
                'sku' => 'KPG-AR-250',
                'product_name' => 'Kopi Susu Gula Aren 250ml',
                'image_url' => 'https://images.unsplash.com/photo-1541167760496-1628856ab772?w=300',
                'buy_price' => 12000.00,
                'sell_price' => 18000.00,
                'stock_quantity' => 100,
                'min_stock_alert' => 10,
                'created_at' => now(),
            ],
            [
                'product_id' => 2,
                'category_id' => 1,
                'sku' => 'KPB-AM-250',
                'product_name' => 'Iced Americano 250ml',
                'image_url' => 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?w=300',
                'buy_price' => 9000.00,
                'sell_price' => 15000.00,
                'stock_quantity' => 85,
                'min_stock_alert' => 10,
                'created_at' => now(),
            ],
            [
                'product_id' => 3,
                'category_id' => 2,
                'sku' => 'RTB-CK-001',
                'product_name' => 'Roti Bakar Cokelat',
                'image_url' => 'https://images.unsplash.com/photo-1509722747041-616f39b57569?w=300',
                'buy_price' => 10000.00,
                'sell_price' => 15000.00,
                'stock_quantity' => 45,
                'min_stock_alert' => 5,
                'created_at' => now(),
            ],
            [
                'product_id' => 4,
                'category_id' => 2,
                'sku' => 'FF-OR-001',
                'product_name' => 'French Fries Original',
                'image_url' => 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=300',
                'buy_price' => 8000.00,
                'sell_price' => 14000.00,
                'stock_quantity' => 60,
                'min_stock_alert' => 10,
                'created_at' => now(),
            ],

            // SLOW MOVING (Stok < 30)
            [
                'product_id' => 5,
                'category_id' => 3,
                'sku' => 'MUG-KC-001',
                'product_name' => 'Cangkir Kaca Premium',
                'image_url' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=300',
                'buy_price' => 15000.00,
                'sell_price' => 25000.00,
                'stock_quantity' => 12,
                'min_stock_alert' => 5,
                'created_at' => now(),
            ],
            [
                'product_id' => 6,
                'category_id' => 3,
                'sku' => 'TMB-SS-500',
                'product_name' => 'Tumbler Stainless Steel 500ml',
                'image_url' => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=300',
                'buy_price' => 27000.00,
                'sell_price' => 45000.00,
                'stock_quantity' => 6,
                'min_stock_alert' => 3,
                'created_at' => now(),
            ],
            [
                'product_id' => 7,
                'category_id' => 2,
                'sku' => 'CRS-AL-001',
                'product_name' => 'Croissant Almond Special',
                'image_url' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=300',
                'buy_price' => 16000.00,
                'sell_price' => 28000.00,
                'stock_quantity' => 9,
                'min_stock_alert' => 3,
                'created_at' => now(),
            ],
            [
                'product_id' => 8,
                'category_id' => 3,
                'sku' => 'TSH-POS-XL',
                'product_name' => 'Kaos Edisi Komunitas Coffee',
                'image_url' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=300',
                'buy_price' => 45000.00,
                'sell_price' => 75000.00,
                'stock_quantity' => 4,
                'min_stock_alert' => 2,
                'created_at' => now(),
            ],
        ]);
    }
}