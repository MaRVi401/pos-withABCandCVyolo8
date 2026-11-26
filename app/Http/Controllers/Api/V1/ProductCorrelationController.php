<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ProductCorrelationService;
use Illuminate\Http\Request;

class ProductCorrelationController extends Controller
{
    protected ProductCorrelationService $correlationService;

    public function __construct(ProductCorrelationService $correlationService)
    {
        $this->correlationService = $correlationService;
    }

    /**
     * GET /api/v1/correlations
     *
     * Query opsional:
     * - min_support    (0-1, default 0.02)
     * - min_confidence (0-1, default 0.2)
     * - min_lift       (default 1)
     * - product_id     (tampilkan hanya korelasi yang melibatkan produk ini)
     * - limit          (1-100, default 20)
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'min_support'    => 'nullable|numeric|min:0|max:1',
            'min_confidence' => 'nullable|numeric|min:0|max:1',
            'min_lift'       => 'nullable|numeric|min:0',
            'product_id'     => 'nullable|integer|exists:products,product_id',
            'limit'          => 'nullable|integer|min:1|max:100',
        ]);

        $result = $this->correlationService->analyze(
            (float) ($validated['min_support'] ?? 0.02),
            (float) ($validated['min_confidence'] ?? 0.2),
            (float) ($validated['min_lift'] ?? 1.0),
            isset($validated['product_id']) ? (int) $validated['product_id'] : null,
            (int) ($validated['limit'] ?? 20)
        );

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json([
            'success'            => true,
            'message'            => 'Korelasi produk berhasil dihitung.',
            'total_transactions' => $result['total_transactions'],
            'total_pairs'        => count($result['data']),
            'data'               => $result['data'],
        ], 200);
    }
}