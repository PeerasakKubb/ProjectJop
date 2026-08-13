<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    protected $fillable = [
        'type', 'title', 'message', 'metadata', 'is_read', 'sent_telegram_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_read' => 'boolean',
            'sent_telegram_at' => 'datetime',
        ];
    }

    public function icon(): string
    {
        return $this->typeLabel();
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'temperature' => 'อุณหภูมิ',
            'humidity' => 'ความชื้น',
            'air_quality' => 'คุณภาพอากาศ',
            'absence' => 'ขาดเรียน',
            'device' => 'อุปกรณ์',
            'attendance' => 'เช็คชื่อ',
            default => 'ระบบ',
        };
    }

    public function badgeColor(): string
    {
        return match ($this->type) {
            'temperature', 'humidity', 'air_quality' => 'badge-danger',
            'absence' => 'badge-warning',
            'device' => 'badge-warning',
            'attendance' => 'badge-success',
            default => 'badge-brand',
        };
    }
}
