<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friendship extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'status',
    ];

    // ===== Quan hệ =====

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // ===== Scopes =====

    /**
     * Lấy tất cả quan hệ bạn bè liên quan đến 1 user (đã chấp nhận)
     */
    public function scopeAcceptedFor($query, int $userId)
    {
        return $query->where('status', 'accepted')
                     ->where(function ($q) use ($userId) {
                         $q->where('sender_id', $userId)
                           ->orWhere('receiver_id', $userId);
                     });
    }

    /**
     * Lấy lời mời kết bạn đang chờ cho 1 user
     */
    public function scopePendingFor($query, int $userId)
    {
        return $query->where('status', 'pending')
                     ->where('receiver_id', $userId);
    }

    /**
     * Lấy lời mời đã gửi
     */
    public function scopeSentBy($query, int $userId)
    {
        return $query->where('status', 'pending')
                     ->where('sender_id', $userId);
    }

    // ===== Static Helpers =====

    /**
     * Kiểm tra 2 user có là bạn bè không
     */
    public static function areFriends(int $userId1, int $userId2): bool
    {
        return static::where('status', 'accepted')
            ->where(function ($q) use ($userId1, $userId2) {
                $q->where(function ($q2) use ($userId1, $userId2) {
                    $q2->where('sender_id', $userId1)->where('receiver_id', $userId2);
                })->orWhere(function ($q2) use ($userId1, $userId2) {
                    $q2->where('sender_id', $userId2)->where('receiver_id', $userId1);
                });
            })->exists();
    }

    /**
     * Lấy trạng thái quan hệ giữa 2 user
     */
    public static function getStatus(int $userId1, int $userId2): ?array
    {
        $friendship = static::where(function ($q) use ($userId1, $userId2) {
            $q->where(function ($q2) use ($userId1, $userId2) {
                $q2->where('sender_id', $userId1)->where('receiver_id', $userId2);
            })->orWhere(function ($q2) use ($userId1, $userId2) {
                $q2->where('sender_id', $userId2)->where('receiver_id', $userId1);
            });
        })->first();

        if (!$friendship) return null;

        return [
            'id' => $friendship->id,
            'status' => $friendship->status,
            'is_sender' => $friendship->sender_id === $userId1,
        ];
    }

    /**
     * Lấy danh sách ID bạn bè của user
     */
    public static function getFriendIds(int $userId): array
    {
        $sent = static::where('sender_id', $userId)
            ->where('status', 'accepted')
            ->pluck('receiver_id');

        $received = static::where('receiver_id', $userId)
            ->where('status', 'accepted')
            ->pluck('sender_id');

        return $sent->merge($received)->unique()->values()->toArray();
    }
}
