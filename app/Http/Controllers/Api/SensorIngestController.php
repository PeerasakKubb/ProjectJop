<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sensor;
use App\Models\SensorReading;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SensorIngestController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'api_key' => 'required|string',
            'value' => 'required|numeric',
            'recorded_at' => 'nullable|date',
        ]);

        $sensor = Sensor::where('api_key', $validated['api_key'])->first();

        if (! $sensor) {
            return response()->json(['message' => 'Invalid sensor API key'], 401);
        }

        $reading = SensorReading::create([
            'sensor_id' => $sensor->id,
            'value' => $validated['value'],
            'recorded_at' => $validated['recorded_at'] ?? now(),
        ]);

        $alert = null;
        if ($sensor->alert_enabled && $sensor->max_threshold && $validated['value'] > $sensor->max_threshold) {
            $alert = "{$sensor->name}: {$validated['value']} {$sensor->unit} (เกินกำหนด {$sensor->max_threshold})";
            try {
                $this->notifications->sensorThresholdAlert(
                    $sensor->name,
                    (float) $validated['value'],
                    $sensor->unit,
                    (float) $sensor->max_threshold,
                    $sensor->room?->name,
                );
            } catch (\Throwable) {
                // อย่าให้แจ้งเตือนภายนอกทำให้ ESP โพสต์ไม่สำเร็จ
            }
        }

        return response()->json([
            'success' => true,
            'reading_id' => $reading->id,
            'alert' => $alert,
        ]);
    }
}
