<?php

use App\Http\Controllers\Api\BackupController;
use App\Http\Controllers\Api\DeviceStatusController;
use App\Http\Controllers\Api\RfidScanController;
use App\Http\Controllers\Api\SensorIngestController;
use App\Http\Middleware\VerifyBackupSecret;
use App\Http\Middleware\VerifyReaderApiKey;
use Illuminate\Support\Facades\Route;

Route::get('/admin/backup', [BackupController::class, 'download'])
    ->middleware(VerifyBackupSecret::class);

Route::post('/rfid/scan', [RfidScanController::class, 'scan'])
    ->middleware(VerifyReaderApiKey::class);

Route::post('/sensors/reading', [SensorIngestController::class, 'store']);
Route::get('/sensors/push', [SensorIngestController::class, 'push']);
Route::get('/sensors/now', [SensorIngestController::class, 'now']);

Route::get('/devices/poll', [DeviceStatusController::class, 'poll']);
Route::get('/devices/poll-lights', [DeviceStatusController::class, 'pollLights']);
Route::post('/devices/lights/set-all', [DeviceStatusController::class, 'setAllLights']);
Route::post('/devices/{device}/status', [DeviceStatusController::class, 'updateStatus']);

Route::post('/telegram/webhook', [\App\Http\Controllers\Api\TelegramWebhookController::class, 'handle']);

Route::post('/line/webhook', [\App\Http\Controllers\Api\LineWebhookController::class, 'handle']);

Route::get('/health', function () {
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $tables = ['sessions', 'users', 'courses', 'cache', 'sensors', 'rfid_readers', 'sensor_readings'];
        $status = [];
        foreach ($tables as $table) {
            $status[$table] = \Illuminate\Support\Facades\Schema::hasTable($table);
        }

        return response()->json([
            'ok' => true,
            'tables' => $status,
            'sensors' => \App\Models\Sensor::query()->count(),
            'rfid_readers' => \App\Models\RfidReader::query()->count(),
            'sensor_readings' => \App\Models\SensorReading::query()->count(),
            'attendance_today' => \App\Models\AttendanceRecord::query()
                ->whereDate('scanned_at', now()->toDateString())
                ->count(),
        ]);
    } catch (\Throwable $e) {
        return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
    }
});
