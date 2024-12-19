<?php

namespace App\Services;

use App\Models\Conversation;
use App\Enums\ConversationStatus;
use Illuminate\Support\Facades\Log;

class QiscusBotService
{
    protected $intents = [
        'kompleks' => ['kompleks', 'rumit', 'sulit'],
        'bantuan_khusus' => ['khusus', 'spesial', 'urgent']
    ];

    public function processMessage($payload)
    {
        // Ekstrak informasi dari payload
        $message = $payload['message'] ?? '';
        $customerId = $payload['customer_id'];

        // Cari atau buat percakapan
        $conversation = $this->findOrCreateConversation($customerId);

        // Proses pesan bot
        return $this->handleBotResponse($conversation, $message);
    }

    protected function findOrCreateConversation($customerId)
    {
        return Conversation::firstOrCreate(
            [
                'customer_id' => $customerId,
                'status' => ConversationStatus::HANDLED_BY_BOT
            ],
            [
                'external_id' => uniqid(),
                'last_message' => null
            ]
        );
    }

    protected function shouldEscalateToHuman($message)
    {
        $lowercaseMessage = strtolower($message);

        // Cek intent yang memerlukan eskalasi manusia
        foreach ($this->intents as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($lowercaseMessage, $keyword)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function handleBotResponse(Conversation $conversation, $message)
    {
        // Logika respons bot sederhana
        $responses = [
            'default' => 'Saya adalah bot otomatis. Saya akan membantu Anda sebisa mungkin.',
            'greeting' => 'Hai! Ada yang bisa saya bantu hari ini?',
        ];

        $botResponse = $this->determineResponse($message, $responses);

        // Simpan percakapan
        $conversation->update([
            'last_message' => $message
        ]);

        return $botResponse;
    }

    protected function determineResponse($message, $responses)
    {
        $lowercaseMessage = strtolower($message);

        if (preg_match('/halo|hai|hello/i', $lowercaseMessage)) {
            return $responses['greeting'];
        }

        return $responses['default'];
    }
}
