<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Analisis korelasi produk (market basket analysis).
 *
 * Untuk setiap pasangan produk (A, B) dihitung:
 * - support    : proporsi transaksi yang memuat A dan B sekaligus
 * - confidence : peluang B dibeli jika A dibeli (dan sebaliknya)
 * - lift       : > 1 berarti A dan B memang cenderung dibeli bersamaan
 */
class ProductCorrelationService
{
    /**
     * Status transaksi yang tidak dihitung (huruf kecil).
     * Sesuaikan dengan nilai status yang dipakai di tabel transactions.
     */
    private array $excludedStatuses = ['cancelled', 'canceled', 'batal', 'void', 'failed'];

    /**
     * Hitung korelasi antar produk dari riwayat transaksi.
     */
    public function analyze(
        float $minSupport = 0.02,
        float $minConfidence = 0.2,
        float $minLift = 1.0,
        ?int $productId = null,
        ?int $limit = null
    ): array {
        $baskets = $this->loadBaskets();
        $totalBaskets = count($baskets);

        if ($totalBaskets === 0) {
            return [
                'success' => false,
                'message' => 'Belum ada data transaksi untuk dianalisis.',
            ];
        }

        $itemCount = [];
        $pairCount = [];

        foreach ($baskets as $items) {
            $items = array_values(array_unique($items));
            sort($items);
            $n = count($items);

            foreach ($items as $id) {
                $itemCount[$id] = ($itemCount[$id] ?? 0) + 1;
            }

            for ($i = 0; $i < $n - 1; $i++) {
                for ($j = $i + 1; $j < $n; $j++) {
                    $key = $items[$i] . '-' . $items[$j];
                    $pairCount[$key] = ($pairCount[$key] ?? 0) + 1;
                }
            }
        }

        $pairs = [];

        foreach ($pairCount as $key => $count) {
            [$a, $b] = array_map('intval', explode('-', $key));

            if ($productId !== null && $a !== $productId && $b !== $productId) {
                continue;
            }

            $support = $count / $totalBaskets;
            $confAtoB = $count / $itemCount[$a];
            $confBtoA = $count / $itemCount[$b];
            $lift = $support / (($itemCount[$a] / $totalBaskets) * ($itemCount[$b] / $totalBaskets));

            if ($support < $minSupport || max($confAtoB, $confBtoA) < $minConfidence || $lift < $minLift) {
                continue;
            }

            $pairs[] = [
                'product_a_id'     => $a,
                'product_b_id'     => $b,
                'times_together'   => $count,
                'support'          => round($support, 4),
                'confidence_a_to_b' => round($confAtoB, 4),
                'confidence_b_to_a' => round($confBtoA, 4),
                'lift'             => round($lift, 4),
            ];
        }

        usort($pairs, function ($x, $y) {
            return [$y['lift'], $y['support']] <=> [$x['lift'], $x['support']];
        });

        if ($limit !== null) {
            $pairs = array_slice($pairs, 0, $limit);
        }

        // Tambahkan nama produk
        $ids = [];
        foreach ($pairs as $p) {
            $ids[] = $p['product_a_id'];
            $ids[] = $p['product_b_id'];
        }

        $names = empty($ids)
            ? collect()
            : DB::table('products')->whereIn('product_id', array_unique($ids))->pluck('product_name', 'product_id');

        foreach ($pairs as &$p) {
            $p['product_a_name'] = $names[$p['product_a_id']] ?? null;
            $p['product_b_name'] = $names[$p['product_b_id']] ?? null;
        }
        unset($p);

        return [
            'success' => true,
            'total_transactions' => $totalBaskets,
            'data' => $pairs,
        ];
    }

    /**
     * Peta korelasi untuk dipakai ABCService.
     * Key: "idKecil-idBesar", value: support, confidence (terbesar dua arah), lift.
     */
    public function getCorrelationMap(
        float $minSupport = 0.02,
        float $minConfidence = 0.2,
        float $minLift = 1.0
    ): array {
        $result = $this->analyze($minSupport, $minConfidence, $minLift);

        if (!$result['success']) {
            return [];
        }

        $map = [];
        foreach ($result['data'] as $p) {
            $map[$p['product_a_id'] . '-' . $p['product_b_id']] = [
                'support'    => $p['support'],
                'confidence' => max($p['confidence_a_to_b'], $p['confidence_b_to_a']),
                'lift'       => $p['lift'],
            ];
        }

        return $map;
    }

    /**
     * Kelompokkan item transaksi menjadi "keranjang" per transaksi.
     * Hasil: [transaction_id => [product_id, ...]]
     */
    private function loadBaskets(): array
    {
        return DB::table('transaction_items as ti')
            ->join('transactions as t', 't.transaction_id', '=', 'ti.transaction_id')
            ->whereNotIn(DB::raw('LOWER(t.status)'), $this->excludedStatuses)
            ->select('ti.transaction_id', 'ti.product_id')
            ->get()
            ->groupBy('transaction_id')
            ->map(fn ($rows) => $rows->pluck('product_id')->map(fn ($id) => (int) $id)->all())
            ->all();
    }
}