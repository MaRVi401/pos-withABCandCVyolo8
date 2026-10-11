<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::table('payment_methods')->truncate();
        DB::table('categories')->truncate();

        Schema::enableForeignKeyConstraints();

        // Payment Methods
        DB::table('payment_methods')->insert([
            ['payment_method_id' => 1, 'method_name' => 'Cash'],
            ['payment_method_id' => 2, 'method_name' => 'QRIS'],
            ['payment_method_id' => 3, 'method_name' => 'Transfer Bank'],
            ['payment_method_id' => 4, 'method_name' => 'Credit / Debit Card'],
        ]);

        // Categories
        DB::table('categories')->insert([
            ['category_id' => 1, 'category_name' => 'Minuman', 'description' => 'Aneka kopi dan olahan minuman segar'],
            ['category_id' => 2, 'category_name' => 'Makanan Ringan', 'description' => 'Snack, kue, dan makanan pendamping'],
            ['category_id' => 3, 'category_name' => 'Merchandise', 'description' => 'Peralatan dan souvenir toko'],
        ]);
    }
}