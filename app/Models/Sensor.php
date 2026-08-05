<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sensor extends Model
{
    protected $fillable = [
        'name', 'type', 'room_id', 'unit', 'min_threshold', 'max_threshold',
        'alert_enabled', 'api_key',
    ];

    protected function casts(): array
    {
        return [
            'alert_enabled' => 'boolean',
            'min_threshold' => 'decimal:2',
            'max_threshold' => 'decimal:2',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function readings(): HasMany
    {
        return $this->hasMany(SensorReading::class);
    }

    public function latestReading()
    {
        return $this->hasOne(SensorReading::class)->latestOfMany('recorded_at');
    }
}
