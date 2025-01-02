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
            'greeting' => 'Selamat datang di layanan JF3 kami. Ada yang bisa saya bantu?',
            'thanks' => 'Terima kasih telah menghubungi kami.',
        ];

    public function processMessage($payload)
    {
        $message = $payload['message']['text'];
        $name = $payload['from']['name'];
        $type = $payload['message']['type'];

        if ($message == 'interactive message') {
            $data =  $payload['message']['extras']['interactive']['nfm_reply']['response_json'];
            $firstName = $this->findFieldByKeyword($data, 'firstName');
        }

        return $this->determineResponse($message, $this->responses, $name);
    }

    function findFieldByKeyword($jsonString, $keyword)
    {
        $data = json_decode($jsonString, true);

        if ($data === null) {
            return null;
        }

        // Loop through all keys and find the one containing our keyword
        foreach ($data as $key => $value) {
            if (stripos($key, $keyword) !== false) {
                return $value;
            }
        }

        return null;
    }

    protected function determineResponse($message, $responses, $name)
    {
        $lowercaseMessage = strtolower($message);
        if (preg_match('/halo|hai|hello/i', $lowercaseMessage)) {
            $response['message'] = $responses['greeting'];
            $response['type'] = 'text';
            return $response;
        } else if (preg_match('/daftar/i', $lowercaseMessage)) {
            $response['type'] = 'template';
            $response['status'] = true;
            $response['payload'] = '{"name":"flow_sign_up","language":{"code":"en"},"components":[{"type":"header","parameters":[{"type":"image","image":{"link":"https://upload.wikimedia.org/wikipedia/commons/thumb/6/6b/WhatsApp.svg/1022px-WhatsApp.svg.png"}}]},{"type":"button","sub_type":"flow","index":"0"}]}';
            return $response;
        } else if (preg_match('/interactive message/i', $lowercaseMessage)) {
            $response['message'] = $responses['thanks'];
            $response['type'] = 'text';
            return $response;
        } else if (preg_match('/webminar/i', $lowercaseMessage)) {
            $response['type'] = 'file_attachment';
            $response['payload'] = '{"url": "https://image-archive.developerhub.io/image/upload/22512/bn3dkt8x2grrv3rpzv9v/1576557437.png","caption": "Undagan untuk mengikuti webinar"}';
            return $response;
        } else {
            $response['message'] = 'Hai ' . $name . ' ' . $responses['default'];
            $response['type'] = 'text';
            return $response;
        }
    }
}
