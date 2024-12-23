<?php

namespace App\Services;

use App\Models\Conversation;
use App\Enums\ConversationStatus;
use Illuminate\Support\Facades\Log;

class QiscusBotService
{
    protected
        $responses = [
            'default' => 'Saya adalah bot otomatis. Saya akan membantu Anda sebisa mungkin.',
            'greeting' => 'Hai! Selamat datang di layanan JF3 kami. Ada yang bisa saya bantu?',
        ];

    public function processMessage($payload)
    {
        $message = $payload['message']['text'];

        return $this->handleBotResponse($message);
    }

    protected function handleBotResponse($message)
    {


        $botResponse = $this->determineResponse($message, $this->responses);

        return $botResponse;
    }

    protected function determineResponse($message, $responses)
    {
        $lowercaseMessage = strtolower($message);

        if (preg_match('/halo|hai|hello/i', $lowercaseMessage)) {
            $response['message'] = $responses['greeting'];
            $response['type'] = 'text';
            return $response;
        } else if (preg_match('/webminar/i', $lowercaseMessage)) {
            $response['type'] = 'file_attachment';
            $response['payload'] = '{"url": "https://image-archive.developerhub.io/image/upload/22512/bn3dkt8x2grrv3rpzv9v/1576557437.png","caption": "Undagan untuk mengikuti webinar"}';
            return $response;
        }

        return $responses['default'];
    }
}
