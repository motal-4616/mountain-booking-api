<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * Các trường có thể gán giá trị hàng loạt
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'current_level',
        'level_discount',
        'is_blocked',
        'avatar',
        'bio',
        'address',
        'date_of_birth',
    ];

    /**
     * Các trường ẩn khi serialize
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Kiểu dữ liệu của các trường
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'current_level' => 'integer',
            'level_discount' => 'decimal:2',
        ];
    }

    /**
     * Lấy thông tin level hiện tại
     */
    public function getLevelInfoAttribute(): ?UserLevel
    {
        return UserLevel::getForLevel($this->current_level ?? 1);
    }

    /**
     * Lấy thông tin level tiếp theo
     */
    public function getNextLevelInfoAttribute(): ?UserLevel
    {
        return UserLevel::getForLevel(($this->current_level ?? 1) + 1);
    }

    /**
     * Kiểm tra user có phải admin không (bao gồm super_admin, admin, booking_manager)
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'booking_manager', 'content_manager']);
    }

    /**
     * Kiểm tra user có phải super_admin không
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Kiểm tra user có phải booking_manager không
     */
    public function isBookingManager(): bool
    {
        return $this->role === 'booking_manager';
    }

    /**
     * Kiểm tra user có phải content_manager không
     */
    public function isContentManager(): bool
    {
        return $this->role === 'content_manager';
    }

    /**
     * Kiểm tra user có quyền quản lý booking không
     * (super_admin, admin, booking_manager đều có quyền)
     */
    public function canManageBookings(): bool
    {
        return in_array($this->role, ['super_admin', 'booking_manager']);
    }

    /**
     * Kiểm tra user có quyền quản lý coupon không
     * (chỉ super_admin)
     */
    public function canManageCoupons(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Kiểm tra user có quyền quản lý nội dung không
     * (super_admin và content_manager có quyền)
     */
    public function canManageContent(): bool
    {
        return in_array($this->role, ['super_admin', 'content_manager']);
    }

    /**
     * Kiểm tra user có quyền quản lý tour và schedule không
     */
    public function canManageTours(): bool
    {
        return in_array($this->role, ['super_admin', 'content_manager']);
    }

    /**
     * Kiểm tra user có quyền quản lý reviews không
     */
    public function canManageReviews(): bool
    {
        return in_array($this->role, ['super_admin', 'content_manager']);
    }

    /**
     * Lấy text hiển thị role
     */
    public function getRoleTextAttribute(): string
    {
        return match($this->role) {
            'super_admin' => 'Super Admin',
            'booking_manager' => 'Quản lý Đặt vé',
            'content_manager' => 'Quản lý Nội dung',
            'user' => 'Người dùng',
            default => 'Không xác định',
        };
    }

    /**
     * Quan hệ: User có nhiều Booking
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Quan hệ: User có nhiều Wishlist
     */
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Quan hệ: User có nhiều Review
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // ===== Social Relationships =====

    /**
     * Quan hệ: User có nhiều bài Blog
     */
    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class);
    }

    /**
     * Quan hệ: User có nhiều nhật ký
     */
    public function journals()
    {
        return $this->hasMany(Journal::class);
    }

    /**
     * Người mà user đang follow
     */
    public function following()
    {
        return $this->belongsToMany(User::class, 'user_follows', 'follower_id', 'following_id')
                    ->withTimestamps();
    }

    /**
     * Người đang follow user
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follows', 'following_id', 'follower_id')
                    ->withTimestamps();
    }

    /**
     * Lời mời kết bạn đã gửi
     */
    public function sentFriendRequests()
    {
        return $this->hasMany(Friendship::class, 'sender_id');
    }

    /**
     * Lời mời kết bạn đã nhận
     */
    public function receivedFriendRequests()
    {
        return $this->hasMany(Friendship::class, 'receiver_id');
    }

    /**
     * Danh sách bạn bè (đã accept)
     */
    public function friends()
    {
        $friendIds = Friendship::getFriendIds($this->id);
        return User::whereIn('id', $friendIds);
    }

    /**
     * Các cuộc hội thoại mà user tham gia
     */
    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
                    ->withPivot(['nickname', 'is_muted', 'last_read_at', 'joined_at'])
                    ->withTimestamps();
    }

    /**
     * Kiểm tra có phải bạn bè của user khác không
     */
    public function isFriendWith(int $userId): bool
    {
        return Friendship::areFriends($this->id, $userId);
    }

    /**
     * Đếm bạn bè
     */
    public function getFriendsCountAttribute(): int
    {
        return count(Friendship::getFriendIds($this->id));
    }

    /**
     * Get user's avatar URL
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            // Check if file exists in storage
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->avatar)) {
                return asset('storage/' . $this->avatar);
            }
            // If avatar path starts with http, return as-is
            if (str_starts_with($this->avatar, 'http')) {
                return $this->avatar;
            }
        }
        
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=2563eb&color=fff';
    }
}
