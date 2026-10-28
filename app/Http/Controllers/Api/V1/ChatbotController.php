<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;
use App\Models\Product;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'history' => 'nullable|array',
        ]);

        $apiKey = env('GROQ_API_KEY');
        $model = env('GROQ_DEFAULT_MODEL', 'llama-3.3-70b-versatile');

        if (!$apiKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'Groq API Key belum dikonfigurasi.'
            ], 500);
        }

        // 1. System Prompt & Konteks AI
        $messages = [
            [
                'role' => 'system',
                'content' => 'Kamu adalah AI Operational Assistant untuk sistem POS (Point of Sale). Tugasmu membantu Admin mengecek laporan penjualan dan stok barang secara real-time. Jika pengguna meminta data penjualan atau stok, panggil fungsi/tool yang sesuai. Jangan pernah mengarang angka transaksi!'
            ]
        ];

        // 2. Masukkan Riwayat Chat jika Ada
        if ($request->has('history') && is_array($request->history)) {
            foreach ($request->history as $chat) {
                if (isset($chat['role']) && isset($chat['content'])) {
                    $messages[] = [
                        'role' => $chat['role'],
                        'content' => $chat['content'],
                    ];
                }
            }
        }

        $messages[] = [
            'role' => 'user',
            'content' => $request->input('message'),
        ];

        // 3. Definisi Tools / Function Calling
        $tools = [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_sales_summary',
                    'description' => 'Mendapatkan ringkasan laporan penjualan berdasarkan periode.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'period' => [
                                'type' => 'string',
                                'enum' => ['today', 'yesterday', 'this_month'],
                                'description' => 'Periode penjualan yang diminta.'
                            ]
                        ],
                        'required' => ['period']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'check_product_stock',
                    'description' => 'Mengecek sisa stok produk berdasarkan nama.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => [
                                'type' => 'string',
                                'description' => 'Nama produk yang dicari.'
                            ]
                        ],
                        'required' => ['query']
                    ]
                ]
            ]
        ];

        try {
            // 4. Request Pertama ke Groq API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'tools' => $tools,
                'tool_choice' => 'auto',
            ]);

            if (!$response->successful()) {
                Log::error('Groq API Error: ' . $response->body());
                return response()->json(['status' => 'error', 'message' => 'Gagal terhubung ke AI.'], 500);
            }

            $responseData = $response->json();
            $choiceMessage = $responseData['choices'][0]['message'];

            // 5. Jika AI Meminta Pemanggilan Tool (Function Calling)
            if (isset($choiceMessage['tool_calls'])) {
                $messages[] = $choiceMessage; // Masukkan instruksi tool dari AI ke riwayat

                foreach ($choiceMessage['tool_calls'] as $toolCall) {
                    $functionName = $toolCall['function']['name'];
                    $arguments = json_decode($toolCall['function']['arguments'], true);
                    $result = null;

                    if ($functionName === 'get_sales_summary') {
                        $result = $this->getSalesSummaryFromDb($arguments['period'] ?? 'today');
                    } elseif ($functionName === 'check_product_stock') {
                        $result = $this->checkStockFromDb($arguments['query'] ?? '');
                    }

                    // Kirim hasil query dari Database kembali ke AI
                    $messages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $toolCall['id'],
                        'content' => json_encode($result)
                    ];
                }

                // Request Kedua ke Groq untuk Menyusun Jawaban Akhir
                $finalResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => $model,
                    'messages' => $messages,
                ]);

                $reply = $finalResponse->json()['choices'][0]['message']['content'] ?? 'Data berhasil diproses.';
            } else {
                $reply = $choiceMessage['content'] ?? 'Tidak ada jawaban dari AI.';
            }

            return response()->json([
                'status' => 'success',
                'reply' => $reply
            ]);
        } catch (\Exception $e) {
            Log::error('Chatbot Exception: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan pada server.'], 500);
        }
    }

    /**
     * Helper Query Laporan Penjualan
     */
    private function getSalesSummaryFromDb($period)
    {
        $query = Transaction::query();

        if ($period === 'today') {
            $query->whereDate('transaction_date', now()->today());
        } elseif ($period === 'yesterday') {
            $query->whereDate('transaction_date', now()->yesterday());
        } elseif ($period === 'this_month') {
            $query->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year);
        }

        $totalTransactions = $query->count();
        $totalRevenue = $query->sum('final_amount');

        return [
            'periode' => $period,
            'total_transaksi' => $totalTransactions,
            'total_pendapatan' => 'Rp ' . number_format($totalRevenue ?? 0, 0, ',', '.')
        ];
    }

    /**
     * Helper Query Stok Produk
     */
    private function checkStockFromDb($search)
    {
        $products = Product::where('product_name', 'like', "%{$search}%")
            ->select('product_name as name', 'stock_quantity as stock', 'sell_price as price')
            ->limit(5)
            ->get();

        if ($products->isEmpty()) {
            return ['message' => 'Produk tidak ditemukan.'];
        }

        return $products->toArray();
    }
}
