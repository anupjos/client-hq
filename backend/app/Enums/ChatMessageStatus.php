<?php

namespace App\Enums;

enum ChatMessageStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
}
