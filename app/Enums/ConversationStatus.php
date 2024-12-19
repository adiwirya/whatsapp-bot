<?php

namespace App\Enums;

enum ConversationStatus: string
{
    case HANDLED_BY_BOT = 'bot';
    case ESCALATED_TO_HUMAN = 'human';
    case RESOLVED = 'resolved';
}
