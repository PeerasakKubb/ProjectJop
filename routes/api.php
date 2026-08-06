<?php

use App\Http\Controllers\Api\DeviceStatusController;
use App\Http\Controllers\Api\RfidScanController;
use App\Http\Controllers\Api\SensorIngestController;
use App\Http\Middleware\VerifyReaderApiKey;
use Illuminate\Support\Facades\Route;

Route::post('/rfid/scan', [RfidScanController::class, 'scan'])
    ->middleware(VerifyReaderApiKey::class);

Route::post('/sensors/reading', [SensorIngestController::class, 'store']);

Route::get('/devices/poll', [DeviceStatusController::class, 'poll']);
Route::get('/devices/poll-lights', [DeviceStatusController::class, 'pollLights']);
Route::post('/devices/{device}/status', [DeviceStatusController::class, 'updateStatus']);

Route::post('/telegram/webhook', [\App\Http\Controllers\Api\TelegramWebhookController::class, 'handle']);

Route::post('/line/webhook', [\App\Http\Controllers\Api\LineWebhookController::class, 'handle']);

Route::get('/health', function () {
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $tables = ['sessions', 'users', 'courses', 'cache'];
        $status = [];
        foreach ($tables as $table) {
            $status[$table] = \Illuminate\Support\Facades\Schema::hasTable($table);
        }

        return response()->json(['ok' => true, 'tables' => $status]);
    } catch (\Throwable $e) {
        return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
    }
});
