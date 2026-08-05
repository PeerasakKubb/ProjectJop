<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramSetWebhookCommand extends Command
{
    protected $signature = 'telegram:set-webhook {url}';

    protected $description = 'ตั้ง Telegram webhook URL';

    public function handle(TelegramService $telegram): int
    {
        if (! $telegram->isConfigured()) {
            $this->error('ตั้ง TELEGRAM_BOT_TOKEN ใน .env ก่อน');

            return self::FAILURE;
        }

        $url = $this->argument('url');

        if ($telegram->setWebhook($url)) {
            $this->info("Webhook set: {$url}");

            return self::SUCCESS;
        }

        $this->error('ตั้ง webhook ไม่สำเร็จ');

        return self::FAILURE;
    }
}
