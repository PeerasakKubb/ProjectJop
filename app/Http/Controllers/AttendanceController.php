<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Room;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $attendanceService) {}

    public function index(Request $request)
    {
        $date = $request->get('date', Carbon::today()->toDateString());
        $roomId = $request->get('room_id');

        $records = $this->attendanceService->getHistory($date, $roomId);
        $rooms = Room::where('is_active', true)->get();
        $stats = $this->attendanceService->todayStats();

        return view('attendance.index', compact('records', 'rooms', 'date', 'roomId', 'stats'));
    }

    public function export(Request $request): StreamedResponse
    {
        $date = $request->get('date');
        $roomId = $request->get('room_id');

        $query = AttendanceRecord::with(['user', 'room'])
            ->where('type', 'in')
            ->orderByDesc('scanned_at');

        if ($date) {
            $query->whereDate('scanned_at', $date);
        }

        if ($roomId) {
            $query->where('room_id', $roomId);
        }

        $filename = 'attendance_' . ($date ?? 'all') . '.csv';

        $statusLabels = [
            'present' => 'มาเรียน',
            'late' => 'มาสาย',
            'absent' => 'ขาดเรียน',
        ];

        return response()->streamDownload(function () use ($query, $statusLabels) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['ชื่อ', 'ห้อง', 'วันที่', 'เวลา', 'สถานะ']);

            $query->chunk(100, function ($records) use ($handle, $statusLabels) {
                foreach ($records as $record) {
                    fputcsv($handle, [
                        $record->user->name,
                        $record->room?->name ?? '-',
                        $record->scanned_at->format('d/m/Y'),
                        $record->scanned_at->format('H:i:s'),
                        $statusLabels[$record->status] ?? $record->status,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function today()
    {
        $records = AttendanceRecord::with('user')
            ->whereDate('scanned_at', Carbon::today())
            ->where('type', 'in')
            ->latest('scanned_at')
            ->get();

        return response()->json($records);
    }
}
