<?php

namespace App\Notifications;

use App\Models\UserLevel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LevelUpNotification extends Notification
{
    use Queueable;

    protected UserLevel $level;

    public function __construct(UserLevel $level)
    {
        $this->level = $level;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $benefits = $this->level->benefits ?? [];
        $benefitsText = is_array($benefits) ? implode(', ', $benefits) : $benefits;

        return [
            'type' => 'level_up',
            'title' => "🎉 Chúc mừng! Bạn đã đạt Level {$this->level->level}",
            'message' => "Bạn đã trở thành {$this->level->icon} {$this->level->name}! Ưu đãi: Giảm {$this->level->discount_percent}% cho mỗi booking.",
            'level' => $this->level->level,
            'level_name' => $this->level->name,
            'level_icon' => $this->level->icon,
            'frame_color' => $this->level->frame_color,
            'discount_percent' => $this->level->discount_percent,
            'benefits' => $benefitsText,
        ];
    }
}
