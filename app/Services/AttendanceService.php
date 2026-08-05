<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\RfidCard;
use App\Models\RfidReader;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function __construct(private NotificationService $notifications) {}

    public function recordScan(string $cardUid, RfidReader $reader): array
    {
        $card = RfidCard::where('card_uid', $cardUid)->where('is_active', true)->first();

        if (! $card) {
            return ['success' => false, 'message' => 'ไม่พบบัตร RFID นี้ในระบบ'];
        }

        $user = $card->user;
        $now = Carbon::now();
        $today = $now->toDateString();

        $existingToday = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('scanned_at', $today)
            ->where('type', 'in')
            ->first();

        if ($existingToday) {
            return [
                'success' => true,
                'message' => 'เช็คชื่อแล้ววันนี้',
                'user' => $user->only(['id', 'name']),
                'scanned_at' => $existingToday->scanned_at->format('H:i:s'),
                'status' => $existingToday->status,
                'duplicate' => true,
            ];
        }

        $status = $this->determineStatus($now);

        $record = AttendanceRecord::create([
            'user_id' => $user->id,
            'rfid_reader_id' => $reader->id,
            'room_id' => $reader->room_id,
            'scanned_at' => $now,
            'type' => 'in',
            'status' => $status,
        ]);

        if ($status === 'late') {
            $this->notifications->lateAttendanceAlert($user->name, $record->scanned_at->format('H:i'));
        }

        return [
            'success' => true,
            'message' => 'บันทึกเวลาเข้าเรียนสำเร็จ',
            'user' => $user->only(['id', 'name']),
            'scanned_at' => $record->scanned_at->format('H:i:s'),
            'status' => $status,
            'duplicate' => false,
        ];
    }

    private function determineStatus(Carbon $time): string
    {
        $lateAfter = Carbon::today()->setTime(8, 15);

        return $time->greaterThan($lateAfter) ? 'late' : 'present';
    }

    public function todayStats(): array
    {
        $today = Carbon::today();

        $records = AttendanceRecord::whereDate('scanned_at', $today)
            ->where('type', 'in')
            ->get();

        return [
            'total' => $records->count(),
            'present' => $records->where('status', 'present')->count(),
            'late' => $records->where('status', 'late')->count(),
        ];
    }

    public function getHistory(?string $date = null, ?int $roomId = null)
    {
        $query = AttendanceRecord::with(['user', 'room', 'rfidReader'])
            ->where('type', 'in')
            ->orderByDesc('scanned_at');

        if ($date) {
            $query->whereDate('scanned_at', $date);
        }

        if ($roomId) {
            $query->where('room_id', $roomId);
        }

        return $query->paginate(20);
    }
}
