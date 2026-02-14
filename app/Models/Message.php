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
            'video' => '🎬 Đã gửi video',
            'voice' => '🎤 Tin nhắn thoại',
            'location' => '📍 Đã chia sẻ vị trí',
            'system' => $this->body ?? 'Thông báo hệ thống',
            default => $this->body ?? '',
        };
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->type !== 'image') return null;
        
        // Đường dẫn ảnh được lưu trong body (không phải metadata)
        $path = $this->body;
        if (!$path) return null;
        if (str_starts_with($path, 'http')) return $path;
        return asset('storage/' . $path);
    }

    public function getVideoUrlAttribute(): ?string
    {
        if ($this->type !== 'video') return null;
        
        $path = $this->body;
        if (!$path) return null;
        if (str_starts_with($path, 'http')) return $path;
        return asset('storage/' . $path);
    }
}
