<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ABCService;

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
     */
    public function generateBundle()
    {
        $result = $this->abcService->optimizeBundling();

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Rekomendasi paket promo lebah ABC berhasil dibuat.',
            'data'    => $result['data']
        ], 200);
    }
}