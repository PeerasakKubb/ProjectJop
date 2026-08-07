<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceStatusController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    /**
     * ESP32 ดวงไฟ poll สถานะ LED ทั้ง 6 ดวงพร้อมกัน
     * GET /api/devices/poll-lights  Header: X-API-Key: lights-station-key
     */
    public function pollLights(Request $request): JsonResponse
    {
        $apiKey = $request->header('X-API-Key') ?: $request->query('api_key');

        if ($apiKey !== 'lights-station-key') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $keys = [
            'led-1-key', 'led-2-key', 'led-3-key',
            'led-4-key', 'led-5-key', 'led-6-key',
        ];

        $devices = collect($keys)
            ->map(fn (string $key) => Device::query()->where('api_key', $key)->first())
            ->filter()
            ->values();

        Device::query()
            ->whereIn('api_key', $keys)
            ->where('is_online', false)
            ->update(['is_online' => true]);

        return response()->json([
            'success' => true,
            'devices' => $devices->map(fn (Device $d) => [
                'id' => $d->id,
                'name' => $d->name,
                'api_key' => $d->api_key,
                'is_on' => $d->is_on,
                'command' => $d->is_on ? 'on' : 'off',
            ])->values(),
        ]);
    }

    /**
     * ตั้งสถานะหลอดไฟทั้ง 6 ดวงจาก station key (สำหรับทดสอบ/ESP)
     * POST /api/devices/lights/set-all  Header: X-API-Key: lights-station-key
     * Body: {"is_on": true}
     */
    public function setAllLights(Request $request): JsonResponse
    {
        $apiKey = $request->header('X-API-Key') ?: $request->query('api_key');

        if ($apiKey !== 'lights-station-key') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'is_on' => 'required|boolean',
        ]);

        $keys = [
            'led-1-key', 'led-2-key', 'led-3-key',
            'led-4-key', 'led-5-key', 'led-6-key',
        ];

        $updated = Device::query()
            ->whereIn('api_key', $keys)
            ->update([
                'is_on' => $validated['is_on'],
                'is_online' => true,
            ]);

        return response()->json([
            'success' => true,
            'updated' => $updated,
            'is_on' => $validated['is_on'],
            'command' => $validated['is_on'] ? 'on' : 'off',
        ]);
    }

    /**
     * ESP32 poll desired relay state (ไม่ต้องใช้ MQTT).
     * GET /api/devices/poll  Header: X-API-Key: device-light-key
     */
    public function poll(Request $request): JsonResponse
    {
        $apiKey = $request->header('X-API-Key') ?: $request->query('api_key');

        if (! $apiKey) {
            return response()->json(['message' => 'API key required'], 401);
        }

        $device = Device::where('api_key', $apiKey)->first();

        if (! $device) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // เครื่องยัง poll อยู่ = ออนไลน์
        if (! $device->is_online) {
            $device->update(['is_online' => true]);
        }

        return response()->json([
            'success' => true,
            'device_id' => $device->id,
            'name' => $device->name,
            'is_on' => $device->is_on,
            'command' => $device->is_on ? 'on' : 'off',
        ]);
    }

    public function updateStatus(Request $request, Device $device): JsonResponse
    {
        if ($request->header('X-API-Key') !== $device->api_key) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'is_on' => 'required|boolean',
            'is_online' => 'nullable|boolean',
        ]);

        $wasOnline = $device->is_online;
        $isOnline = $validated['is_online'] ?? true;

        $device->update([
            'is_on' => $validated['is_on'],
            'is_online' => $isOnline,
        ]);

        if ($wasOnline && ! $isOnline) {
            $this->notifications->deviceOfflineAlert(
                $device->name,
                $device->room?->name,
            );
        }

        return response()->json(['success' => true, 'device' => $device->fresh()]);
    }
}
