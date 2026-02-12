<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    /**
     * Các trường có thể gán giá trị hàng loạt
     */
    protected $fillable = [
        'tour_id',
        'departure_date',
        'end_date',
        'max_people',
        'available_slots',
        'price',
        'min_people',
        'registration_deadline_days',
        'is_active',
    ];

    /**
     * Kiểu dữ liệu của các trường
     */
    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'end_date' => 'date',
            'price' => 'decimal:0',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Appended attributes
     */
    protected $appends = [
        'is_available',
        'is_full',
        'can_register',
    ];

    /**
     * Quan hệ: Schedule thuộc về Tour
     */
    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    /**
     * Quan hệ: Schedule có nhiều Booking
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Kiểm tra còn chỗ trống không
     */
    public function hasAvailableSlots(int $quantity = 1): bool
    {
        return $this->available_slots >= $quantity;
    }

    /**
     * Check if schedule is available (active, has slots, not past deadline)
     */
    public function getIsAvailableAttribute(): bool
    {
        return $this->is_active 
            && $this->available_slots > 0 
            && !$this->isRegistrationClosed()
            && $this->departure_date >= now();
    }

    /**
     * Check if schedule is full
     */
    public function getIsFullAttribute(): bool
    {
        return $this->available_slots <= 0;
    }

    /**
     * Check if can register for this schedule
     */
    public function getCanRegisterAttribute(): bool
    {
        return $this->is_available;
    }

    /**
     * Format ngày khởi hành
     */
    public function getFormattedDateAttribute(): string
    {
        return \Carbon\Carbon::parse($this->departure_date)->format('d/m/Y');
    }

    /**
     * Format ngày kết thúc
     */
    public function getFormattedEndDateAttribute(): ?string
    {
        return $this->end_date ? \Carbon\Carbon::parse($this->end_date)->format('d/m/Y') : null;
    }

    /**
     * Tính số ngày tour
     */
    public function getDurationDaysAttribute(): int
    {
        if (!$this->end_date) {
            return 1;
        }
        return \Carbon\Carbon::parse($this->departure_date)->diffInDays(\Carbon\Carbon::parse($this->end_date)) + 1;
    }

    /**
     * Hiển thị thời gian tour
     */
    public function getDurationTextAttribute(): string
    {
        $days = $this->duration_days;
        return $days . ' ngày' . ($days > 1 ? ' ' . ($days - 1) . ' đêm' : '');
    }

    /**
     * Trạng thái của lịch trình
     */
    public function getStatusTextAttribute(): string
    {
        $now = now();
        $departure = \Carbon\Carbon::parse($this->departure_date);
        $end = $this->end_date ? \Carbon\Carbon::parse($this->end_date) : $departure;

        if ($end < $now) {
            return 'Đã kết thúc';
        } elseif ($departure <= $now && $end >= $now) {
            return 'Đang diễn ra';
        } elseif ($departure <= $now->addDays(7)) {
            return 'Sắp diễn ra';
        }
        return 'Chưa bắt đầu';
    }

    /**
     * Badge class cho trạng thái
     */
    public function getStatusBadgeAttribute(): string
    {
        $now = now();
        $departure = \Carbon\Carbon::parse($this->departure_date);
        $end = $this->end_date ? \Carbon\Carbon::parse($this->end_date) : $departure;

        if ($end < $now) {
            return 'secondary';
        } elseif ($departure <= $now && $end >= $now) {
            return 'primary';
        } elseif ($departure <= $now->addDays(7)) {
            return 'warning';
        }
        return 'info';
    }

    /**
     * Tính số ngày còn lại đến tour
     */
    public function getDaysUntilDepartureAttribute(): int
    {
        $departure = \Carbon\Carbon::parse($this->departure_date);
        $now = now();
        
        if ($departure < $now) {
            return 0;
        }
        
        return $now->diffInDays($departure);
    }

    /**
     * Hiển thị thời gian còn lại đến tour (human readable)
     */
    public function getTimeUntilDepartureAttribute(): string
    {
        $departure = \Carbon\Carbon::parse($this->departure_date);
        $now = now();
        
        if ($departure < $now) {
            if ($this->end_date && \Carbon\Carbon::parse($this->end_date) >= $now) {
                return 'Đang diễn ra';
            }
            return 'Đã kết thúc';
        }
        
        $days = $this->days_until_departure;
        
        if ($days == 0) {
            $hours = $now->diffInHours($departure);
            if ($hours < 1) {
                $minutes = $now->diffInMinutes($departure);
                return "Còn {$minutes} phút";
            }
            return "Còn {$hours} giờ";
        } elseif ($days == 1) {
            return 'Khởi hành vào ngày mai';
        } elseif ($days <= 7) {
            return "Còn {$days} ngày";
        } elseif ($days <= 30) {
            $weeks = ceil($days / 7);
            return "Còn {$weeks} tuần";
        } else {
            $months = floor($days / 30);
            return "Còn {$months} tháng";
        }
    }

    /**
     * Kiểm tra có đủ số lượng người tối thiểu không
     */
    public function hasMinimumParticipants(): bool
    {
        // Lấy min_people từ schedule, ưu tiên hơn tour
        $minPeople = $this->min_people ?? $this->tour->min_people ?? 10;
        $bookedPeople = $this->max_people - $this->available_slots;
        
        return $bookedPeople >= $minPeople;
    }

    /**
     * Lấy số người đã đặt
     */
    public function getBookedPeopleAttribute(): int
    {
        return $this->max_people - $this->available_slots;
    }

    /**
     * Lấy số người tối thiểu
     */
    public function getMinPeopleAttribute($value): int
    {
        return $value ?? $this->tour->min_people ?? 10;
    }

    /**
     * Lấy ngày deadline đăng ký (registration_deadline_date)
     */
    public function getRegistrationDeadlineAttribute(): \Carbon\Carbon
    {
        $days = $this->registration_deadline_days ?? 2;
        return \Carbon\Carbon::parse($this->departure_date)->subDays($days);
    }

    /**
     * Kiểm tra đã hết hạn đăng ký chưa
     */
    public function isRegistrationClosed(): bool
    {
        return now() >= $this->registration_deadline;
    }

    /**
     * Accessor: Kiểm tra đã hết hạn đăng ký chưa
     */
    public function getIsRegistrationClosedAttribute(): bool
    {
        return $this->isRegistrationClosed();
    }

    /**
     * Số ngày còn lại để đăng ký
     */
    public function getDaysUntilRegistrationDeadlineAttribute(): int
    {
        $deadline = $this->registration_deadline;
        $now = now();
        
        if ($deadline < $now) {
            return 0;
        }
        
        return $now->diffInDays($deadline);
    }

    /**
     * Hiển thị thời gian còn lại để đăng ký (human readable)
     */
    public function getTimeUntilRegistrationDeadlineAttribute(): string
    {
        $deadline = $this->registration_deadline;
        $now = now();
        
        if ($deadline < $now) {
            return 'Đã hết hạn đăng ký';
        }
        
        $days = $this->days_until_registration_deadline;
        
        if ($days == 0) {
            $hours = $now->diffInHours($deadline);
            if ($hours < 1) {
                $minutes = $now->diffInMinutes($deadline);
                return "Còn {$minutes} phút để đăng ký";
            }
            return "Còn {$hours} giờ để đăng ký";
        } elseif ($days == 1) {
            return 'Đóng đăng ký vào ngày mai';
        } elseif ($days <= 7) {
            return "Còn {$days} ngày để đăng ký";
        } else {
            return "Còn " . ceil($days / 7) . " tuần để đăng ký";
        }
    }
}
