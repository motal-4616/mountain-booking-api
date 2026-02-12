<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'avatar',
        'last_message_id',
    ];

    // ===== Quan hệ =====

    public function participants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
                    ->withPivot(['nickname', 'is_muted', 'last_read_at', 'joined_at']);
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    public function lastMessage()
    {
        return $this->belongsTo(Message::class, 'last_message_id');
    }

    // ===== Helpers =====

    /**
     * Tìm hoặc tạo cuộc hội thoại private giữa 2 user
     */
    public static function findOrCreatePrivate(int $userId1, int $userId2): self
    {
        // Tìm cuộc hội thoại private đã tồn tại giữa 2 user
        $conversation = static::where('type', 'private')
            ->whereHas('participants', function ($q) use ($userId1) {
                $q->where('user_id', $userId1);
            })
            ->whereHas('participants', function ($q) use ($userId2) {
                $q->where('user_id', $userId2);
            })
            ->first();

        if ($conversation) return $conversation;

        // Tạo mới
        $conversation = static::create(['type' => 'private']);
        $conversation->participants()->createMany([
            ['user_id' => $userId1],
            ['user_id' => $userId2],
        ]);

        return $conversation;
    }

    /**
     * Lấy user đối diện trong cuộc hội thoại private
     */
    public function getOtherUser(int $currentUserId): ?User
    {
        if ($this->type !== 'private') return null;

        return $this->users()->where('users.id', '!=', $currentUserId)->first();
    }

    /**
     * Đếm tin nhắn chưa đọc cho 1 user
     */
    public function unreadCountFor(int $userId): int
    {
        $participant = $this->participants()->where('user_id', $userId)->first();
        if (!$participant || !$participant->last_read_at) {
            return $this->messages()->where('user_id', '!=', $userId)->count();
        }

        return $this->messages()
            ->where('user_id', '!=', $userId)
            ->where('created_at', '>', $participant->last_read_at)
            ->count();
    }

    /**
     * Kiểm tra user có tham gia cuộc hội thoại không
     */
    public function hasParticipant(int $userId): bool
    {
        return $this->participants()->where('user_id', $userId)->exists();
    }
}
