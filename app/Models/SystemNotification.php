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
        return match ($this->type) {
            'temperature', 'humidity', 'air_quality' => '🌡️',
            'absence' => '📋',
            'device' => '💡',
            'attendance' => '✅',
            default => '🔔',
        };
    }

    public function badgeColor(): string
    {
        return match ($this->type) {
            'temperature', 'humidity', 'air_quality' => 'bg-red-100 text-red-700',
            'absence' => 'bg-yellow-100 text-yellow-800',
            'device' => 'bg-orange-100 text-orange-800',
            default => 'bg-blue-100 text-blue-700',
        };
    }
}
