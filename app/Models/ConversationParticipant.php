<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversationParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'nickname',
        'is_muted',
        'last_read_at',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'is_muted' => 'boolean',
            'last_read_at' => 'datetime',
            'joined_at' => 'datetime',
        ];
    }

    // ===== Quan hệ =====

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
