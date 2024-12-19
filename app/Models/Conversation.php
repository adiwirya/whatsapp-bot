<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\ConversationStatus;

class Conversation extends Model
{
    protected $fillable = [
        'external_id',
        'customer_id',
        'status',
        'last_message',
        'agent_id'
    ];

    protected $casts = [
        'status' => ConversationStatus::class
    ];
}
