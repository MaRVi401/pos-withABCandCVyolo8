<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder OPSIONAL: membuat 150 transaksi dummy dengan pola pembelian berpasangan
 * berdasarkan NAMA produk (bukan urutan kategori), termasuk pasangan
 * produk cepat laku + produk lambat laku supaya bisa diuji oleh ABC.
 *
 * Aman dijalankan berulang: transaksi dummy lama (INV-DUMMY-*) dihapus dulu.
 * Jalankan: php artisan db:seed --class=DummyTransactionSeeder
 */
class DummyTransactionSeeder extends Seeder
{
    /**
     * Pasangan yang sering dibeli bersamaan: [kata kunci produk A, kata kunci produk B, bobot].
     * Kata kunci dicocokkan dengan sebagian nama produk (tidak peka huruf besar/kecil).
     */
    private array $affinities = [
        ['kopi susu', 'croissant', 30], // kopi + roti/pastry (cepat + lambat)
        ['americano', 'croissant', 20], // kopi + roti/pastry (cepat + lambat)
        ['kopi susu', 'roti bakar', 10], // cepat + cepat
        ['americano', 'french fries', 10], // cepat + cepat
        ['kopi susu', 'cangkir', 5],    // minuman + merchandise (cepat + lambat)
        ['americano', 'tumbler', 5],    // minuman + merchandise (cepat + lambat)
    ];

    public function run(): void
    {
        $adminId = DB::table('users')->where('role_id', 1)->value('user_id');

        if (!$adminId) {
            $this->command->warn('Tidak ada user admin (role_id = 1). Seeder dibatalkan.');
            return;
        }

        $products = DB::table('products')->get(['product_id', 'product_name', 'sell_price']);

        if ($products->count() < 2) {
            $this->command->warn('Produk kurang dari 2. Isi tabel products dulu.');
            return;
        }

        // Ubah daftar pasangan berbasis kata kunci menjadi pasangan produk nyata
        $pairs = [];
        foreach ($this->affinities as [$keyA, $keyB, $weight]) {
            $a = $products->first(fn ($p) => stripos($p->product_name, $keyA) !== false);
            $b = $products->first(fn ($p) => stripos($p->product_name, $keyB) !== false);

            if ($a && $b && $a->product_id !== $b->product_id) {
                $pairs[] = ['a' => $a, 'b' => $b, 'weight' => $weight];
            }
        }

        $totalWeight = array_sum(array_column($pairs, 'weight'));

        // Hapus transaksi dummy lama (transaction_items ikut terhapus via ON DELETE CASCADE)
        DB::table('transactions')->where('invoice_number', 'like', 'INV-DUMMY-%')->delete();

        for ($n = 1; $n <= 150; $n++) {
            $picked = collect();

            // ~80% transaksi berisi salah satu pasangan (dipilih menurut bobot)
            if ($totalWeight > 0 && mt_rand(1, 100) <= 80) {
                $roll = mt_rand(1, $totalWeight);
                foreach ($pairs as $pair) {
                    $roll -= $pair['weight'];
                    if ($roll <= 0) {
                        $picked->push($pair['a'], $pair['b']);
                        break;
                    }
                }
            }

            // Tambah produk acak agar data tidak terlalu seragam
            if ($picked->isEmpty() || mt_rand(1, 100) <= 25) {
                $picked->push($products->random());
            }

            $picked = $picked->unique('product_id')->values();

            $items = [];
            $total = 0;

            foreach ($picked as $product) {
                $qty = mt_rand(1, 2);
                $subtotal = $qty * (float) $product->sell_price;
                $total += $subtotal;

                $items[] = [
                    'product_id' => $product->product_id,
                    'quantity'   => $qty,
                    'unit_price' => $product->sell_price,
                    'subtotal'   => $subtotal,
                ];
            }

            $transactionId = DB::table('transactions')->insertGetId([
                'invoice_number'   => 'INV-DUMMY-' . str_pad((string) $n, 4, '0', STR_PAD_LEFT),
                'admin_id'         => $adminId,
                'customer_id'      => null,
                'transaction_date' => now()->subDays(mt_rand(0, 60))->setTime(mt_rand(8, 21), mt_rand(0, 59)),
                'total_amount'     => $total,
                'discount_amount'  => 0,
                'tax_amount'       => 0,
                'final_amount'     => $total,
                'status'           => 'completed', // samakan dengan nilai status yang dipakai di aplikasi Anda
            ], 'transaction_id');

            foreach ($items as &$item) {
                $item['transaction_id'] = $transactionId;
            }
            unset($item);

            DB::table('transaction_items')->insert($items);
        }

        $this->command->info('150 transaksi dummy berhasil dibuat.');
    }
}