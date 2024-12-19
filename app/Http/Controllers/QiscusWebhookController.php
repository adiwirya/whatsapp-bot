<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\QiscusBotService;
use Illuminate\Support\Facades\Log;

class QiscusWebhookController extends Controller
{
    protected $botService;

    public function __construct(QiscusBotService $botService)
    {
        $this->botService = $botService;
    }

    public function handleWebhook(Request $request)
    {
        try {
            // Validasi payload dari Qiscus
            $payload = $this->validatePayload($request);

            // Proses pesan menggunakan bot service
            $response = $this->botService->processMessage($payload);

            // Kirim balasan
            return response()->json([
                'message' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('Webhook Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Terjadi kesalahan dalam memproses pesan'
            ], 500);
        }
    }

    protected function validatePayload(Request $request)
    {
        Log::info('Incoming Webhook: ' . $request->getContent());

        $payload = $request->validate([
            'message' => 'required|string',
            'customer_id' => 'required|string'
        ]);

        return $payload;
    }
}
