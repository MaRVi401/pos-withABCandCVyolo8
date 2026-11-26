<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ABCService;
use Illuminate\Http\Request;

class ABCController extends Controller
{
    protected ABCService $abcService;

    public function __construct(ABCService $abcService)
    {
        $this->abcService = $abcService;
    }

    /**
     * Endpoint API untuk memicu perhitungan lebah ABC
     * GET /api/v1/abc/generate-bundle
     *
     * Query opsional (semua boleh dikosongkan):
     * - min_support, min_confidence, min_lift : ambang batas korelasi produk
     * - require_correlation (true/false)      : wajib berkorelasi atau tidak
     */
    public function generateBundle(Request $request)
    {
        $validated = $request->validate([
            'min_support'         => 'nullable|numeric|min:0|max:1',
            'min_confidence'      => 'nullable|numeric|min:0|max:1',
            'min_lift'            => 'nullable|numeric|min:0',
            'require_correlation' => 'nullable|boolean',
        ]);

        $result = $this->abcService->optimizeBundling($validated);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Rekomendasi paket promo lebah ABC berhasil dibuat.',
            'data'    => $result['data'],
            'meta'    => $result['meta'] ?? null,
        ], 200);
    }
}