<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    /**
     * Handle incoming chat message and send to Groq API.
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'history' => 'nullable|array', 
        ]);

        $apiKey = env('GROQ_API_KEY');
        $model = env('GROQ_DEFAULT_MODEL');

        if (!$apiKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'Groq API Key belum dikonfigurasi di environment.'
            ], 500);
        }

        // Susun pesan dasar dengan System Prompt (konteks sistem POS/Aplikasi Anda)
        $messages = [
            [
                'role' => 'system',
                'content' => 'Anda adalah asisten cerdas AI untuk sistem POS (Point of Sale). Bantu pengguna jawab pertanyaan seputar transaksi, stok, dan kasir dengan bahasa yang ramah dan lugas.'
            ]
        ];

        // Masukkan riwayat percakapan jika ada
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

        // Masukkan pesan terbaru pengguna
        $messages[] = [
            'role' => 'user',
            'content' => $request->input('message'),
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 1000,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['choices'][0]['message']['content'] ?? 'Tidak ada respon dari model.';

                return response()->json([
                    'status' => 'success',
                    'reply' => $reply,
                    'usage' => $data['usage'] ?? null,
                ]);
            }

            Log::error('Groq API Error: ' . $response->body());

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mendapatkan respon dari Groq API.',
                'details' => $response->json(),
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('Chatbot Exception: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server saat menghubungi Groq API.',
            ], 500);
        }
    }
}