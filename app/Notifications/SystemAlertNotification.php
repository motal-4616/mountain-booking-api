<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Thông báo cảnh báo hệ thống
 * Gửi cho: super_admin
 */
class SystemAlertNotification extends Notification
{
    use Queueable;

    protected string $title;
    protected string $message;
    protected string $level;

    /**
     * @param string $title Tiêu đề
     * @param string $message Nội dung chi tiết
     * @param string $level Mức độ: info, warning, error, critical
     */
    public function __construct(string $title, string $message, string $level = 'error')
    {
        $this->title = $title;
        $this->message = $message;
        $this->level = $level;
    }

    /**
     * Kênh gửi thông báo
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Dữ liệu lưu vào database
     */
    public function toArray(object $notifiable): array
    {
        $iconMap = [
            'info' => 'bi-info-circle',
            'warning' => 'bi-exclamation-triangle',
            'error' => 'bi-x-octagon',
            'critical' => 'bi-exclamation-octagon',
        ];
        
        $colorMap = [
            'info' => 'info',
            'warning' => 'warning',
            'error' => 'danger',
            'critical' => 'danger',
        ];
        
        return [
            'type' => 'system_alert',
            'title' => $this->title,
            'message' => $this->message,
            'level' => $this->level,
            'icon' => $iconMap[$this->level] ?? 'bi-exclamation-triangle',
            'color' => $colorMap[$this->level] ?? 'danger',
            'priority' => in_array($this->level, ['error', 'critical']) ? 'high' : 'normal',
            'url' => route('admin.dashboard'),
        ];
    }
}
