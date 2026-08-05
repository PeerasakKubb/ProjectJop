<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    protected $fillable = [
        'name', 'type', 'room_id', 'mqtt_topic', 'is_on', 'is_online', 'api_key',
    ];

    protected function casts(): array
    {
        return [
            'is_on' => 'boolean',
            'is_online' => 'boolean',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function commands(): HasMany
    {
        return $this->hasMany(DeviceCommand::class);
    }

    public function scopeClassroomLights($query)
    {
        return $query->where('type', 'light')
            ->where(function ($q) {
                $q->where('api_key', 'like', 'led-%-key')
                    ->orWhere('name', 'like', 'หลอดไฟห้องเรียน%')
                    ->orWhere('name', 'like', 'LED %');
            })
            ->orderBy('id');
    }
}
