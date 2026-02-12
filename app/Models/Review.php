<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tour_id',
        'booking_id',
        'rating',
        'comment',
        'title',
        'status',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Quan hệ: Review thuộc về User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quan hệ: Review thuộc về Tour
     */
    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    /**
     * Quan hệ: Review có thể thuộc về Booking
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Scope: Chỉ lấy reviews đã được duyệt
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: Chỉ lấy reviews đang chờ duyệt
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Accessor: Lấy badge status
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'approved' => '<span class="badge bg-success">Đã duyệt</span>',
            'rejected' => '<span class="badge bg-danger">Từ chối</span>',
            'pending' => '<span class="badge bg-warning">Chờ duyệt</span>',
            default => '<span class="badge bg-secondary">Không xác định</span>',
        };
    }

    /**
     * Accessor: Hiển thị số sao
     */
    public function getStarsHtmlAttribute()
    {
        $html = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $this->rating) {
                $html .= '<i class="bi bi-star-fill text-warning"></i>';
            } else {
                $html .= '<i class="bi bi-star text-muted"></i>';
            }
        }
        return $html;
    }
}
