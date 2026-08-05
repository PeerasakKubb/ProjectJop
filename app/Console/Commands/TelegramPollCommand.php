<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramPollCommand extends Command
{
    protected $signature = 'telegram:poll';

    protected $description = 'รับข้อความ Telegram แบบ long polling (สำหรับ local dev)';

    public function handle(TelegramService $telegram): int
    {
        if (! $telegram->isConfigured()) {
            $this->error('ตั้ง TELEGRAM_BOT_TOKEN ใน .env ก่อน');

            return self::FAILURE;
        }

        $this->info('Telegram polling started... (Ctrl+C เพื่อหยุด)');
        $offset = null;

        while (true) {
            $updates = $telegram->getUpdates($offset);

            foreach ($updates as $update) {
                $offset = ($update['update_id'] ?? 0) + 1;
                $telegram->handleUpdate($update);
            }
        }
    }
}
