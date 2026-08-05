<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RfidScanController extends Controller
{
    public function __construct(private AttendanceService $attendanceService) {}

    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'card_uid' => 'required|string|max:50',
        ]);

        $reader = $request->attributes->get('rfid_reader');
        $result = $this->attendanceService->recordScan($validated['card_uid'], $reader);

        $statusCode = $result['success'] ? 200 : 404;

        return response()->json($result, $statusCode);
    }
}
