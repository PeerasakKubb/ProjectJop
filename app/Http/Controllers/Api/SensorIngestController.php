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

    /**
     * ESP-A ส่งค่าแบบ GET เหมือนไฟ ESP-B ที่ใช้ได้จริงบน Render
     * GET /api/sensors/push?temp=28.4&humidity=57
     */
    public function push(Request $request): JsonResponse
    {
        $temp = $request->query('temp', $request->input('temp'));
        $humidity = $request->query('humidity', $request->input('humidity'));
        $ids = [];

        if ($temp !== null && $temp !== '') {
            $ids['temp'] = $this->writeReading('sensor-temp-key', (float) $temp);
        }
        if ($humidity !== null && $humidity !== '') {
            $ids['humidity'] = $this->writeReading('sensor-humidity-key', (float) $humidity);
        }

        if ($ids === []) {
            return response()->json(['message' => 'temp or humidity required'], 422);
        }

        return response()->json(['success' => true, 'reading_ids' => $ids]);
    }

    public function now(): JsonResponse
    {
        $rows = Sensor::with('latestReading')->get()->map(function (Sensor $sensor) {
            $reading = $sensor->latestReading;

            return [
                'id' => $sensor->id,
                'value' => $reading ? round((float) $reading->value, 1) : null,
                'unit' => $sensor->unit,
                'recorded_at' => $reading?->recorded_at?->toJSON(),
            ];
        });

        return response()->json($rows)->header('Cache-Control', 'no-store, no-cache');
    }

    private function writeReading(string $apiKey, float $value): ?int
    {
        $sensor = Sensor::where('api_key', $apiKey)->first();
        if (! $sensor) {
            return null;
        }

        $reading = SensorReading::create([
            'sensor_id' => $sensor->id,
            'value' => $value,
            'recorded_at' => now(),
        ]);

        return $reading->id;
    }
}
