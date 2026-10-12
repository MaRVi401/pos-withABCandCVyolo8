<?php

namespace App\Services;

use App\Models\Product;

class ABCService
{
    private int $numBees = 20;
    private int $maxIter = 50;

    /**
     * Menjalankan optimasi Artificial Bee Colony (ABC) multi-kriteria
     */
    public function optimizeBundling(): array
    {
        $columns = ['product_id', 'product_name', 'buy_price', 'sell_price', 'stock_quantity'];

        // FILTER: Hanya ambil produk yang stoknya > 0
        $fastProducts = Product::select($columns)
            ->where('stock_quantity', '>=', 30)
            ->get();

        $slowProducts = Product::select($columns)
            ->where('stock_quantity', '>', 0) // Pastikan stok slow-moving masih tersedia (> 0)
            ->where('stock_quantity', '<', 30)
            ->get();

        if ($fastProducts->isEmpty() || $slowProducts->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Stok produk tidak mencukupi untuk membuat paket promo bundling.'
            ];
        }

        $bestBundle = null;
        $maxFitnessScore = -INF;

        for ($iter = 0; $iter < $this->maxIter; $iter++) {
            foreach ($fastProducts as $fast) {
                foreach ($slowProducts as $slow) {
                    
                    // Double check memastikan kedua produk memiliki stok > 0
                    if ($fast->stock_quantity <= 0 || $slow->stock_quantity <= 0) {
                        continue;
                    }

                    $normalPrice = (float) $fast->sell_price + (float) $slow->sell_price;
                    $totalBuyPrice = (float) $fast->buy_price + (float) $slow->buy_price;

                    $discountRate = mt_rand(5, 15) / 100;
                    $discountAmount = $normalPrice * $discountRate;
                    $promoPrice = $normalPrice - $discountAmount;

                    $profit = $promoPrice - $totalBuyPrice;

                    if ($profit <= 0) {
                        continue;
                    }

                    $urgencyScore = (100 - $slow->stock_quantity) * 150;
                    $attractivenessBonus = ($discountRate >= 0.08) ? 2000 : 0;

                    $fitness = $profit + $urgencyScore + $attractivenessBonus;

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