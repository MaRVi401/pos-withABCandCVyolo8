<?php

namespace App\Services;

use App\Models\Product;

class ABCService
{
    private int $numBees = 20;         // Jumlah populasi lebah buatan
    private int $maxIter = 50;         // Maksimum siklus iterasi (perulangan lebah)
    private float $minDiscount = 0.05; // Batas diskon minimal 5%
    private float $maxDiscount = 0.15; // Batas diskon maksimal 15%

    /**
     * Menjalankan optimasi Artificial Bee Colony (ABC) multi-kriteria
     */
    public function optimizeBundling(): array
    {
        // 1. Ambil kolom penting saja untuk efisiensi memori (Fast-moving vs Slow-moving)
        $columns = ['product_id', 'product_name', 'buy_price', 'sell_price', 'stock_quantity'];

        $fastProducts = Product::select($columns)->where('stock_quantity', '>=', 30)->get();
        $slowProducts = Product::select($columns)->where('stock_quantity', '<', 30)->get();

        if ($fastProducts->isEmpty() || $slowProducts->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Data produk tidak mencukupi untuk optimasi bundling (dibutuhkan variasi produk stok tinggi dan rendah).'
            ];
        }

        $bestBundle = null;
        $maxFitnessScore = -INF;

        // 2. Simulasi Perulangan Lebah Pekerja (Employed) & Pengamat (Onlooker)
        for ($iter = 0; $iter < $this->maxIter; $iter++) {
            foreach ($fastProducts as $fast) {
                foreach ($slowProducts as $slow) {
                    
                    // a. Hitung komponen harga
                    $normalPrice = (float) $fast->sell_price + (float) $slow->sell_price;
                    $totalBuyPrice = (float) $fast->buy_price + (float) $slow->buy_price;

                    // b. Eksplorasi Diskon Acak oleh Lebah di rentang 5% sampai 15%
                    $discountRate = mt_rand(5, 15) / 100;
                    $discountAmount = $normalPrice * $discountRate;
                    $promoPrice = $normalPrice - $discountAmount;

                    // c. Hitung Profit Bersih Pasangan
                    $profit = $promoPrice - $totalBuyPrice;

                    // Abaikan jika pilihan diskon menyebabkan kerugian modal (profit <= 0)
                    if ($profit <= 0) {
                        continue;
                    }

                    // d. MULTI-CRITERIA FITNESS FUNCTION:
                    $urgencyScore = (100 - $slow->stock_quantity) * 150;
                    $attractivenessBonus = ($discountRate >= 0.08) ? 2000 : 0;

                    $fitness = $profit + $urgencyScore + $attractivenessBonus;

                    // e. Seleksi Solusi Terbaik
                    if ($fitness > $maxFitnessScore) {
                        $maxFitnessScore = $fitness;
                        $bestBundle = [
                            'fast_product' => [
                                'product_id'   => $fast->product_id,
                                'product_name' => $fast->product_name,
                                'sell_price'   => (float) $fast->sell_price,
                                'stock'        => $fast->stock_quantity,
                            ],
                            'slow_product' => [
                                'product_id'   => $slow->product_id,
                                'product_name' => $slow->product_name,
                                'sell_price'   => (float) $slow->sell_price,
                                'stock'        => $slow->stock_quantity,
                            ],
                            'normal_price'     => (float) $normalPrice,
                            'discount_rate'    => ($discountRate * 100) . '%',
                            'discount_amount'  => (float) $discountAmount,
                            'promo_price'      => (float) $promoPrice,
                            'estimated_profit' => (float) $profit,
                            'fitness_score'    => round($fitness, 2),
                        ];
                    }
                }
            }
        }

        if (!$bestBundle) {
            return [
                'success' => false,
                'message' => 'Tidak ditemukan pasangan paket promo yang menguntungkan modal toko.'
            ];
        }

        return [
            'success' => true,
            'data'    => $bestBundle
        ];
    }
}