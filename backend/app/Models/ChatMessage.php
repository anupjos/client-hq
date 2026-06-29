<?php

namespace App\Models;

use App\Enums\ChatMessageRole;
use App\Enums\ChatMessageStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_id', 'user_id', 'role', 'content', 'status', 'error'])]
class ChatMessage extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'role' => ChatMessageRole::class,
            'status' => ChatMessageStatus::class,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
