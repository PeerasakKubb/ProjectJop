<?php

namespace App\Services;

use App\Models\Device;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    public function isConfigured(): bool
    {
        return ! empty(config('services.telegram.bot_token'));
    }

    public function apiUrl(string $method): string
    {
        return 'https://api.telegram.org/bot' . config('services.telegram.bot_token') . '/' . $method;
    }

    public function sendMessage(int|string $chatId, string $text, ?array $replyMarkup = null): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if ($replyMarkup) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        $response = Http::post($this->apiUrl('sendMessage'), $payload);

        if (! $response->successful()) {
            Log::warning('Telegram sendMessage failed', ['body' => $response->body()]);
        }

        return $response->successful();
    }

    public function answerCallbackQuery(string $callbackQueryId, string $text = ''): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        Http::post($this->apiUrl('answerCallbackQuery'), [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
        ]);
    }

    public function handleUpdate(array $update): void
    {
        if (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query']);

            return;
        }

        if (! isset($update['message']['text'])) {
            return;
        }

        $chatId = $update['message']['chat']['id'];
        $text = trim($update['message']['text']);

        if (str_starts_with($text, '/')) {
            $this->handleCommand($chatId, $text);
        }
    }

    public function handleCommand(int|string $chatId, string $text): void
    {
        $parts = explode(' ', $text, 2);
        $command = strtolower($parts[0]);
        $argument = $parts[1] ?? '';

        match ($command) {
            '/start' => $this->cmdStart($chatId),
            '/help' => $this->cmdHelp($chatId),
            '/link' => $this->cmdLink($chatId, trim($argument)),
            '/devices' => $this->cmdDevices($chatId),
            '/status' => $this->cmdStatus($chatId),
            default => $this->sendMessage($chatId, "ไม่รู้จักคำสั่ง\nพิมพ์ /help ดูคำสั่งทั้งหมด"),
        };
    }

    private function cmdStart(int|string $chatId): void
    {
        $user = $this->findLinkedUser($chatId);

        $text = "🏫 <b>Smart Classroom Bot</b>\n\n";
        $text .= "ควบคุมอุปกรณ์ในห้องเรียนผ่าน Telegram\n\n";

        if ($user) {
            $text .= "✅ เชื่อมบัญชีแล้ว: {$user->name}\n";
            $text .= "พิมพ์ /devices เพื่อควบคุมอุปกรณ์";
        } else {
            $text .= "1. ไปที่หน้า Profile บนเว็บ\n";
            $text .= "2. กดสร้างรหัสเชื่อมต่อ\n";
            $text .= "3. พิมพ์ /link รหัส6หลัก";
        }

        $this->sendMessage($chatId, $text);
    }

    private function cmdHelp(int|string $chatId): void
    {
        $this->sendMessage($chatId, implode("\n", [
            '<b>คำสั่งที่ใช้ได้</b>',
            '/start - เริ่มต้น',
            '/link รหัส - เชื่อมบัญชี',
            '/devices - ควบคุมอุปกรณ์',
            '/status - ดูสถานะทั้งหมด',
            '/help - วิธีใช้',
        ]));
    }

    private function cmdLink(int|string $chatId, string $code): void
    {
        if (strlen($code) !== 6) {
            $this->sendMessage($chatId, '❌ รูปแบบไม่ถูกต้อง ใช้: /link 123456');

            return;
        }

        $userId = Cache::pull('telegram_link:' . $code);

        if (! $userId) {
            $this->sendMessage($chatId, '❌ รหัสหมดอายุหรือไม่ถูกต้อง กรุณาสร้างรหัสใหม่จากหน้า Profile');

            return;
        }

        $user = User::find($userId);

        if (! $user) {
            $this->sendMessage($chatId, '❌ ไม่พบผู้ใช้');

            return;
        }

        User::where('telegram_chat_id', $chatId)->update(['telegram_chat_id' => null]);
        $user->update(['telegram_chat_id' => (string) $chatId]);

        $this->sendMessage($chatId, "✅ เชื่อมบัญชีสำเร็จ!\nยินดีต้อนรับ {$user->name}\n\nพิมพ์ /devices เพื่อควบคุมอุปกรณ์");
    }

    private function cmdDevices(int|string $chatId): void
    {
        if (! $this->authorize($chatId)) {
            return;
        }

        $devices = Device::with('room')->get();

        if ($devices->isEmpty()) {
            $this->sendMessage($chatId, 'ยังไม่มีอุปกรณ์ในระบบ');

            return;
        }

        $buttons = $devices->map(fn (Device $d) => [[
            'text' => ($d->is_on ? '🟢' : '⚫') . " {$d->name}",
            'callback_data' => "toggle:{$d->id}",
        ]])->values()->all();

        $this->sendMessage($chatId, '🔌 <b>เลือกอุปกรณ์เพื่อเปิด/ปิด</b>', [
            'inline_keyboard' => $buttons,
        ]);
    }

    private function cmdStatus(int|string $chatId): void
    {
        if (! $this->authorize($chatId)) {
            return;
        }

        $devices = Device::with('room')->get();
        $lines = ["📊 <b>สถานะอุปกรณ์</b>", ''];

        foreach ($devices as $device) {
            $state = $device->is_on ? '🟢 เปิด' : '⚫ ปิด';
            $online = $device->is_online ? '' : ' (ออฟไลน์)';
            $room = $device->room?->name ?? '-';
            $lines[] = "{$device->name} [{$room}]: {$state}{$online}";
        }

        $this->sendMessage($chatId, implode("\n", $lines));
    }

    private function handleCallbackQuery(array $callback): void
    {
        $chatId = $callback['message']['chat']['id'];
        $data = $callback['data'] ?? '';
        $callbackId = $callback['id'];

        if (! str_starts_with($data, 'toggle:')) {
            $this->answerCallbackQuery($callbackId, 'คำสั่งไม่รู้จัก');

            return;
        }

        if (! $this->authorize($chatId)) {
            $this->answerCallbackQuery($callbackId, 'ไม่มีสิทธิ์');

            return;
        }

        $deviceId = (int) str_replace('toggle:', '', $data);
        $device = Device::find($deviceId);

        if (! $device) {
            $this->answerCallbackQuery($callbackId, 'ไม่พบอุปกรณ์');

            return;
        }

        $user = $this->findLinkedUser($chatId);
        $device = app(DeviceService::class)->toggle($device, $user, 'telegram');

        $state = $device->is_on ? 'เปิด' : 'ปิด';
        $this->answerCallbackQuery($callbackId, "{$device->name}: {$state}");
        $this->sendMessage($chatId, "✅ {$state} <b>{$device->name}</b> แล้ว");
    }

    private function authorize(int|string $chatId): bool
    {
        $user = $this->findLinkedUser($chatId);

        if (! $user) {
            $this->sendMessage($chatId, '❌ กรุณาเชื่อมบัญชีก่อน ด้วยคำสั่ง /link รหัสจากหน้า Profile');

            return false;
        }

        if (! $user->isAdmin() && ! $user->isTeacher()) {
            $this->sendMessage($chatId, '❌ เฉพาะ Admin/อาจารย์เท่านั้นที่ควบคุมอุปกรณ์ได้');

            return false;
        }

        return true;
    }

    private function findLinkedUser(int|string $chatId): ?User
    {
        return User::where('telegram_chat_id', (string) $chatId)->first();
    }

    public static function generateLinkCode(int $userId): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put('telegram_link:' . $code, $userId, now()->addMinutes(10));

        return $code;
    }

    public function setWebhook(string $url): bool
    {
        $response = Http::post($this->apiUrl('setWebhook'), ['url' => $url]);

        return $response->successful();
    }

    public function getUpdates(?int $offset = null): array
    {
        $params = ['timeout' => 30];
        if ($offset) {
            $params['offset'] = $offset;
        }

        $response = Http::get($this->apiUrl('getUpdates'), $params);

        return $response->json('result', []);
    }
}
