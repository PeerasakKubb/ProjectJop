<?php

namespace App\Services;

use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function __construct(
        private TelegramService $telegram,
        private LineService $line,
    ) {}

    public function alert(
        string $type,
        string $title,
        string $message,
        ?array $metadata = null,
        bool $sendTelegram = true,
        ?string $cooldownKey = null,
        int $cooldownMinutes = 30,
    ): ?SystemNotification {
        if ($cooldownKey && Cache::has("alert_cooldown:{$cooldownKey}")) {
            return null;
        }

        $notification = SystemNotification::create([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'metadata' => $metadata,
        ]);

        if ($sendTelegram) {
            $pushMessage = "{$title}\n{$message}";
            $sent = false;

            if ($this->telegram->isConfigured()) {
                $telegramMessage = "<b>{$title}</b>\n{$message}";

                User::whereNotNull('telegram_chat_id')
                    ->whereIn('role', ['admin', 'teacher'])
                    ->each(function (User $user) use ($telegramMessage, &$sent) {
                        if ($this->telegram->sendMessage($user->telegram_chat_id, $telegramMessage)) {
                            $sent = true;
                        }
                    });
            }

            if ($this->line->isConfigured()) {
                User::whereNotNull('line_user_id')
                    ->whereIn('role', ['admin', 'teacher'])
                    ->each(function (User $user) use ($pushMessage, &$sent) {
                        if ($this->line->sendText($user->line_user_id, $pushMessage)) {
                            $sent = true;
                        }
                    });
            }

            if ($sent) {
                $notification->update(['sent_telegram_at' => now()]);
            }
        }

        if ($cooldownKey) {
            Cache::put("alert_cooldown:{$cooldownKey}", true, now()->addMinutes($cooldownMinutes));
        }

        return $notification;
    }

    public function sensorThresholdAlert(string $sensorName, float $value, string $unit, float $threshold, ?string $room = null): void
    {
        $roomText = $room ? " ({$room})" : '';

        $this->alert(
            type: 'temperature',
            title: 'แจ้งเตือนค่าวัดผิดปกติ',
            message: "{$sensorName}{$roomText}: {$value} {$unit} เกินกำหนด {$threshold} {$unit}",
            metadata: ['sensor' => $sensorName, 'value' => $value, 'threshold' => $threshold],
            cooldownKey: 'sensor:' . md5($sensorName),
            cooldownMinutes: 15,
        );
    }

    public function deviceOfflineAlert(string $deviceName, ?string $room = null): void
    {
        $roomText = $room ? " ห้อง{$room}" : '';

        $this->alert(
            type: 'device',
            title: 'อุปกรณ์ออฟไลน์',
            message: "{$deviceName}{$roomText} ไม่ตอบสนองหรือขาดการเชื่อมต่อ",
            cooldownKey: 'device_offline:' . md5($deviceName),
            cooldownMinutes: 60,
        );
    }

    public function deviceStatusAlert(string $deviceName, bool $isOn, ?string $room = null): void
    {
        $state = $isOn ? 'เปิด' : 'ปิด';
        $roomText = $room ? " ({$room})" : '';

        $this->alert(
            type: 'device',
            title: 'สถานะอุปกรณ์เปลี่ยน',
            message: "{$deviceName}{$roomText} ถูก{$state}",
            sendTelegram: false,
        );
    }

    public function lateAttendanceAlert(string $studentName, string $time): void
    {
        $this->alert(
            type: 'attendance',
            title: 'นักเรียนมาสาย',
            message: "{$studentName} เช็คชื่อเวลา {$time} (มาสาย)",
            sendTelegram: false,
        );
    }

    public function checkAbsentStudents(): int
    {
        if (Cache::has('absent_check:' . now()->toDateString())) {
            return 0;
        }

        $studentIds = User::where('role', 'student')->pluck('id');
        $presentIds = \App\Models\AttendanceRecord::whereDate('scanned_at', today())
            ->where('type', 'in')
            ->pluck('user_id');

        $absent = User::whereIn('id', $studentIds->diff($presentIds))->get();

        if ($absent->isEmpty()) {
            Cache::put('absent_check:' . now()->toDateString(), true, now()->endOfDay());

            return 0;
        }

        $names = $absent->pluck('name')->join(', ');

        $this->alert(
            type: 'absence',
            title: 'แจ้งเตือนนักเรียนขาดเรียน',
            message: "วันนี้ขาดเรียน {$absent->count()} คน: {$names}",
            metadata: ['absent_ids' => $absent->pluck('id')->toArray()],
            cooldownKey: 'absent:' . now()->toDateString(),
            cooldownMinutes: 720,
        );

        Cache::put('absent_check:' . now()->toDateString(), true, now()->endOfDay());

        return $absent->count();
    }

    public function unreadCount(): int
    {
        return SystemNotification::where('is_read', false)->count();
    }
}
