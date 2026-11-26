<?php

namespace App\Services;

use App\Models\Product;

class ABCService
{
    private int $numBees = 20;
    private int $maxIter = 50;

    /**
     * Bobot fungsi fitness. Semua komponen berskala 0-1 (tanpa satuan rupiah),
     * sehingga produk mahal tidak otomatis menang dan korelasi menjadi kriteria utama.
     */
    private const W_CORRELATION = 0.60; // seberapa sering dibeli bersamaan (support, dinormalisasi)
    private const W_URGENCY     = 0.25; // makin sedikit stok produk lambat, makin mendesak
    private const W_MARGIN      = 0.10; // margin keuntungan paket setelah diskon
    private const W_ATTRACTIVE  = 0.05; // bonus kecil untuk diskon >= 8%

    protected ProductCorrelationService $correlationService;

    public function __construct(ProductCorrelationService $correlationService)
    {
        $this->correlationService = $correlationService;
    }

    /**
     * Menjalankan optimasi Artificial Bee Colony (ABC) multi-kriteria
     *
     * Secara bawaan paket HANYA dipilih dari pasangan produk yang berkorelasi
     * (sering dibeli bersamaan, mis. kopi + roti). Kirim require_correlation=false
     * untuk mengizinkan pasangan tanpa korelasi.
     *
     * @param array $options min_support, min_confidence, min_lift, require_correlation
     */
    public function optimizeBundling(array $options = []): array
    {
        $minSupport         = (float) ($options['min_support'] ?? 0.02);
        $minConfidence      = (float) ($options['min_confidence'] ?? 0.2);
        $minLift            = (float) ($options['min_lift'] ?? 1.0);
        $requireCorrelation = (bool) ($options['require_correlation'] ?? true);

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

        // Korelasi produk dari riwayat transaksi (kopi + roti, dst.)
        $correlations = $this->correlationService->getCorrelationMap($minSupport, $minConfidence, $minLift);

        // Kumpulkan pasangan kandidat (produk cepat x produk lambat)
        $candidates = [];
        $maxSupport = 0.0;

        foreach ($fastProducts as $fast) {
            foreach ($slowProducts as $slow) {
                $a = (int) $fast->product_id;
                $b = (int) $slow->product_id;
                $corr = $correlations[min($a, $b) . '-' . max($a, $b)] ?? null;

                if ($requireCorrelation && $corr === null) {
                    continue; // hanya pasangan berkorelasi
                }

                if ($corr !== null) {
                    $maxSupport = max($maxSupport, $corr['support']);
                }

                $candidates[] = ['fast' => $fast, 'slow' => $slow, 'corr' => $corr];
            }
        }

        if (empty($candidates)) {
            return [
                'success' => false,
                'message' => 'Belum ada pasangan produk cepat-lambat yang sering dibeli bersamaan. '
                    . 'Pastikan data transaksi cukup (banyak transaksi berisi lebih dari satu produk), '
                    . 'atau turunkan min_support / min_confidence.'
            ];
        }

        $best = null;
        $bestFitness = -INF;

        for ($iter = 0; $iter < $this->maxIter; $iter++) {
            foreach ($candidates as $cand) {
                $fast = $cand['fast'];
                $slow = $cand['slow'];
                $corr = $cand['corr'];

                $normalPrice = (float) $fast->sell_price + (float) $slow->sell_price;
                $totalBuyPrice = (float) $fast->buy_price + (float) $slow->buy_price;

                $discountRate = mt_rand(5, 15) / 100;
                $discountAmount = $normalPrice * $discountRate;
                $promoPrice = $normalPrice - $discountAmount;

                $profit = $promoPrice - $totalBuyPrice;

                if ($profit <= 0) {
                    continue;
                }

                // Semua komponen berskala 0-1
                $correlationScore = ($corr !== null && $maxSupport > 0) ? $corr['support'] / $maxSupport : 0.0;
                $urgencyScore     = max(0, min(1, (100 - $slow->stock_quantity) / 100));
                $marginScore      = $profit / $promoPrice;
                $attractiveness   = ($discountRate >= 0.08) ? 1.0 : 0.0;

                $fitness = (self::W_CORRELATION * $correlationScore)
                    + (self::W_URGENCY * $urgencyScore)
                    + (self::W_MARGIN * $marginScore)
                    + (self::W_ATTRACTIVE * $attractiveness);

                if ($fitness > $bestFitness) {
                    $bestFitness = $fitness;
                    $best = [
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
                        'correlation'      => $corr, // support, confidence, lift (null jika tanpa korelasi)
                        'reason'           => $this->buildReason($fast->product_name, $slow->product_name, $corr),
                        'fitness_score'    => round($fitness * 100, 2), // skala 0-100
                    ];
                }
            }
        }

        if (!$best) {
            return [
                'success' => false,
                'message' => 'Tidak ditemukan pasangan paket promo yang menguntungkan modal toko.'
            ];
        }

        return [
            'success' => true,
            'data'    => $best,
            'meta'    => [
                'require_correlation'    => $requireCorrelation,
                'correlation_matched'    => $best['correlation'] !== null,
                'total_correlated_pairs' => count($correlations),
                'total_candidates'       => count($candidates),
            ],
        ];
    }

    /**
     * Kalimat alasan yang bisa ditampilkan di UI / dijelaskan ke dosen.
     */
    private function buildReason(string $nameA, string $nameB, ?array $corr): string
    {
        if ($corr === null) {
            return "{$nameA} + {$nameB} dipilih tanpa dukungan data korelasi.";
        }

        return sprintf(
            '%s + %s dibeli bersamaan di %s%% transaksi (peluang beli bersama %s%%, lift %s).',
            $nameA,
            $nameB,
            round($corr['support'] * 100, 1),
            round($corr['confidence'] * 100),
            round($corr['lift'], 2)
        );
    }
}