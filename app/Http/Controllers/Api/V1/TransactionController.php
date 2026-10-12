<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    /**
     * Memproses Transaksi Checkout Kasir (POS)
     */
    public function store(Request $request)
    {
        // 1. Validasi Input Payload
        $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,payment_method_id',
            'paid_amount'       => 'required|numeric|min:0',
            'discount_amount'   => 'nullable|numeric|min:0',
            'customer_id'       => 'nullable|exists:users,user_id',
            'items'             => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,product_id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        try {
            // 2. Transaksi Atomik Database
            $transactionResult = DB::transaction(function () use ($request) {
                $totalAmount = 0;
                $itemsToInsert = [];
                $productsToUpdate = [];

                // a. Cek Stok & Hitung Subtotal
                foreach ($request->items as $itemData) {
                    $product = Product::where('product_id', $itemData['product_id'])
                        ->lockForUpdate()
                        ->first();

                    if ($product->stock_quantity < $itemData['quantity']) {
                        throw new \Exception("Stok produk '{$product->product_name}' tidak mencukupi. Sisa stok: {$product->stock_quantity}.");
                    }

                    $unitPrice = (float) $product->sell_price;
                    $subtotal = $unitPrice * $itemData['quantity'];
                    $totalAmount += $subtotal;

                    $itemsToInsert[] = [
                        'product_id' => $product->product_id,
                        'quantity'   => $itemData['quantity'],
                        'unit_price' => $unitPrice,
                        'subtotal'   => $subtotal,
                    ];

                    $productsToUpdate[] = [
                        'model'     => $product,
                        'new_stock' => $product->stock_quantity - $itemData['quantity'],
                    ];
                }

                // b. Hitung Diskon, Total Akhir, dan Kembalian
                $discount = (float) ($request->discount_amount ?? 0);
                $finalAmount = max(0, $totalAmount - $discount);
                $paidAmount = (float) $request->paid_amount;

                if ($paidAmount < $finalAmount) {
                    throw new \Exception("Uang pembayaran (Rp " . number_format($paidAmount, 0, ',', '.') . ") kurang dari total belanja (Rp " . number_format($finalAmount, 0, ',', '.') . ").");
                }

                $changeAmount = $paidAmount - $finalAmount;
                $invoiceNumber = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(4));
                $currentDate = now();

                // c. Simpan Ke Tabel 'transactions' (Presisi Sesuai Migrasi)
                $transaction = Transaction::create([
                    'invoice_number'   => $invoiceNumber,
                    'admin_id'         => $request->user()->user_id, // ID Admin/Kasir Login
                    'customer_id'      => $request->customer_id ?? null,
                    'transaction_date' => $currentDate,
                    'total_amount'     => $totalAmount,
                    'discount_amount'  => $discount,
                    'tax_amount'       => 0.00,
                    'final_amount'     => $finalAmount,
                    'status'           => 'COMPLETED',
                ]);

                // d. Simpan Item Detail
                foreach ($itemsToInsert as $item) {
                    TransactionItem::create([
                        'transaction_id' => $transaction->transaction_id,
                        'product_id'     => $item['product_id'],
                        'quantity'       => $item['quantity'],
                        'unit_price'     => $item['unit_price'],
                        'subtotal'       => $item['subtotal'],
                    ]);
                }

                // e. Eksekusi Pemotongan Stok Produk
                foreach ($productsToUpdate as $pData) {
                    $pData['model']->update([
                        'stock_quantity' => $pData['new_stock']
                    ]);
                }

                // f. Simpan Ke Tabel 'payments' (Sesuai Kolom Migrasi)
                Payment::create([
                    'transaction_id'    => $transaction->transaction_id,
                    'payment_method_id' => $request->payment_method_id,
                    'amount_paid'       => $paidAmount,
                    'change_amount'     => $changeAmount,
                    'payment_status'    => 'SUCCESS',
                    'payment_date'      => $currentDate,
                ]);

                return [
                    'transaction'      => $transaction,
                    'paid_amount'       => $paidAmount,
                    'change_amount'     => $changeAmount,
                    'transaction_date' => $currentDate->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Transaksi checkout kasir berhasil diproses.',
                'data'    => [
                    'transaction_id'   => $transactionResult['transaction']->transaction_id,
                    'invoice_number'   => $transactionResult['transaction']->invoice_number,
                    'total_amount'     => (float) $transactionResult['transaction']->total_amount,
                    'discount_amount'  => (float) $transactionResult['transaction']->discount_amount,
                    'final_amount'     => (float) $transactionResult['transaction']->final_amount,
                    'paid_amount'      => (float) $transactionResult['paid_amount'],
                    'change_amount'    => (float) $transactionResult['change_amount'],
                    'transaction_date' => $transactionResult['transaction_date'],
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Mengambil Riwayat Transaksi Penjualan
     */
    public function index()
    {
        $transactions = Transaction::with([
            'admin:user_id,name',
            'customer:user_id,name',
            'items.product:product_id,product_name,sku'
        ])
        ->orderBy('transaction_date', 'desc')
        ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $transactions
        ]);
    }
}