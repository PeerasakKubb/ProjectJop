<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class CheckAbsentStudentsCommand extends Command
{
    protected $signature = 'notifications:check-absents';

    protected $description = 'ตรวจนักเรียนที่ขาดเรียนวันนี้และส่งแจ้งเตือน';

    public function handle(NotificationService $notifications): int
    {
        $count = $notifications->checkAbsentStudents();

        $this->info($count > 0
            ? "แจ้งเตือนนักเรียนขาดเรียน {$count} คน"
            : 'ไม่มีนักเรียนขาดเรียน หรือแจ้งเตือนแล้ววันนี้');

        return self::SUCCESS;
    }
}
