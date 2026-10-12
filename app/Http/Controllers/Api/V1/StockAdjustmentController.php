<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function index()
    {
        $adjustments = StockAdjustment::with([
            'product:product_id,product_name,sku',
            'user:user_id,name'
        ])
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'data'    => $adjustments
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id'      => 'required|exists:products,product_id',
            'adjustment_type' => 'required|in:IN,OUT,CORRECTION',
            'quantity'        => 'required|integer|min:1',
            'reason'          => 'required|string|max:255'
        ]);

        return DB::transaction(function () use ($validated) {
            $product = Product::where('product_id', $validated['product_id'])->lockForUpdate()->first();

            $oldStock = $product->stock_quantity;
            $newStock = $oldStock;

            if ($validated['adjustment_type'] === 'IN') {
                $newStock = $oldStock + $validated['quantity'];
            } elseif ($validated['adjustment_type'] === 'OUT') {
                if ($oldStock < $validated['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal! Jumlah pengurangan stok melebihi sisa stok saat ini (' . $oldStock . ').'
                    ], 422);
                }
                $newStock = $oldStock - $validated['quantity'];
            } elseif ($validated['adjustment_type'] === 'CORRECTION') {
                $newStock = $validated['quantity'];
            }

            // Update stok pada tabel products
            $product->update(['stock_quantity' => $newStock]);

            // Catat riwayat penyesuaian sesuai skema database
            $adjustment = StockAdjustment::create([
                'product_id'       => $product->product_id,
                'user_id'          => Auth::id(),
                'adjustment_type'  => $validated['adjustment_type'],
                'quantity_changed' => $validated['quantity'],
                'reason'           => $validated['reason'],
                'created_at'       => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Penyesuaian stok berhasil disimpan!',
                'data'    => $adjustment->load(['product', 'user'])
            ], 201);
        });
    }
}