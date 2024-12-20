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
        $message = $payload['message']['text'];

        return $this->handleBotResponse($message);
    }

    protected function handleBotResponse($message)
    {
        // Logika respons bot sederhana
        $responses = [
            'default' => 'Saya adalah bot otomatis. Saya akan membantu Anda sebisa mungkin.',
            'greeting' => 'Hai! Ada yang bisa saya bantu hari ini?',
        ];

        $botResponse = $this->determineResponse($message, $responses);

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
