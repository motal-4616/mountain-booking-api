<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'type',
        'body',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
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

    // ===== Accessors =====

    public function getPreviewAttribute(): string
    {
        return match ($this->type) {
            'text' => \Illuminate\Support\Str::limit($this->body, 50),
            'image' => '📷 Đã gửi ảnh',
            'voice' => '🎤 Tin nhắn thoại',
            'location' => '📍 Đã chia sẻ vị trí',
            'system' => $this->body ?? 'Thông báo hệ thống',
            default => $this->body ?? '',
        };
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->type !== 'image' || !$this->metadata) return null;
        $path = $this->metadata['image_path'] ?? null;
        if (!$path) return null;
        if (str_starts_with($path, 'http')) return $path;
        return asset('storage/' . $path);
    }
}
