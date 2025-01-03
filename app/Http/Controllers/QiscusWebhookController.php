<?php

namespace App\Http\Controllers;

use App\Services\QiscusBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;


class QiscusWebhookController extends Controller
{
    protected $botService;
    protected $appId;
    protected $secretKey;
    protected $baseUrl;
    protected $email;
    protected $channelId;

    public function __construct(QiscusBotService $botService)
    {
        $this->botService = $botService;

        $this->appId = env('QISCUS_APP_ID');
        $this->secretKey = env('QISCUS_SECRET_KEY');
        $this->baseUrl = env('QISCUS_BASE_URL');
        $this->email = env('QISCUS_EMAIL');
        $this->channelId = env('QISCUS_CHANNEL_ID');

        $this->validateConfig();
    }

    private function validateConfig()
    {
        if (empty($this->appId) || empty($this->secretKey) || empty($this->baseUrl) || empty($this->email) || empty($this->channelId)) {
            Log::error('Qiscus configuration missing', [
                'app_id' => $this->appId,
                'base_url' => $this->baseUrl,
                'email' => $this->email,
                'channel_id' => $this->channelId
            ]);
            throw new \Exception('Qiscus configuration is incomplete');
        }
    }

    protected function getHeaders()
    {
        return [
            'Content-Type' => 'application/json',
            'QISCUS-APP-ID' => $this->appId,
            'QISCUS-SECRET-KEY' => $this->secretKey
        ];
    }

    protected function makeUrl()
    {
        return $this->baseUrl . $this->appId . '/bot';
    }

    protected function makeWAUrl()
    {
        return $this->baseUrl . 'whatsapp/v1/' . $this->appId . '/' . $this->channelId . '/messages';
    }



    public function handleWebhook(Request $request)
    {
        try {
            // Validasi payload dari Qiscus
            $payload = $this->validatePayload($request);
            // Proses pesan menggunakan bot service
            $response = $this->botService->processMessage($payload);
            // Kirim balasan
            if ($response['status'] == true) {
                $status = $this->sendWAResponse($response, $payload['from']['email']);
            } else {
                $status = $this->sendResponse($response, $payload['room']['id']);
            }
            return response()->json([
                'message' => $status
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

        // $whatsapp = WhatsappMessage::fromJson($request->getContent());
        $payload = $request->validate([
            'type' => 'required|string',
            'payload' => 'required|array',
            'payload.message.text' => 'required|string',
            'payload.room.id' => 'required|string',
            'payload.from.email' => 'required|string',
            'payload.from.name' => 'required|string',
            'payload.message.type' => 'required|string',
            'payload.room.name' => 'required|string'
        ]);

        return $payload['payload'];
    }

    private function sendResponse($response, $roomId)
    {
        $url = $this->makeUrl();
        $headers = $this->getHeaders();

        $data = [
            'room_id' => $roomId,
            'message' => $response['message'] ?? '',
            'type' => $response['type'] ?? 'text',
            'sender_email' => $this->email,
            'payload' => $response['payload'] ?? ''

        ];
        Log::info('Bot Response Data: ' . json_encode($data));
        $response = Http::withHeaders($headers)
            ->post($url, $data);

        Log::info('Bot Response: ' . $response->getBody());

        return $response->getBody();
    }

    private function sendWAResponse($response, $no)
    {
        $url = $this->makeWAUrl();
        $headers = $this->getHeaders();
        $payload = json_decode($response['payload'], true);
        $data = [
            'recipient_type' => 'individual',
            'type' => $response['type'] ?? 'text',
            'to' => $no,
            $response['type'] ?? 'text' => $payload ?? ''

        ];
        Log::info('Bot WA Response Data: ' . json_encode($data));
        $response = Http::withHeaders($headers)
            ->post($url, $data);

        Log::info('Bot Response WA: ' . $response->getBody());

        return $response->getBody();
    }
}
