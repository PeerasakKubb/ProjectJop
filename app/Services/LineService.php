<?php

namespace App\Services;

use App\Models\Device;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LineService
{
    public function isConfigured(): bool
    {
        return ! empty(config('services.line.channel_access_token'));
    }

    public function verifySignature(string $body, ?string $signature): bool
    {
        $secret = config('services.line.channel_secret');

        if (! $secret || ! $signature) {
            return false;
        }

        $hash = base64_encode(hash_hmac('sha256', $body, $secret, true));

        return hash_equals($hash, $signature);
    }

    public function reply(string $replyToken, array $messages): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $response = Http::withToken(config('services.line.channel_access_token'))
            ->post('https://api.line.me/v2/bot/message/reply', [
                'replyToken' => $replyToken,
                'messages' => $messages,
            ]);

        if (! $response->successful()) {
            Log::warning('LINE reply failed', ['body' => $response->body()]);
        }

        return $response->successful();
    }

    public function push(string $userId, array $messages): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $response = Http::withToken(config('services.line.channel_access_token'))
            ->post('https://api.line.me/v2/bot/message/push', [
                'to' => $userId,
                'messages' => $messages,
            ]);

        if (! $response->successful()) {
            Log::warning('LINE push failed', ['body' => $response->body()]);
        }

        return $response->successful();
    }

    public function sendText(string $userId, string $text): bool
    {
        return $this->push($userId, [['type' => 'text', 'text' => $text]]);
    }

    public function handleWebhook(array $events): void
    {
        foreach ($events as $event) {
            $type = $event['type'] ?? '';

            if ($type === 'message' && ($event['message']['type'] ?? '') === 'text') {
                $this->handleTextMessage($event);

                continue;
            }

            if ($type === 'postback') {
                $this->handlePostback($event);
            }
        }
    }

    private function handleTextMessage(array $event): void
    {
        $replyToken = $event['replyToken'] ?? '';
        $userId = $event['source']['userId'] ?? '';
        $text = trim($event['message']['text'] ?? '');

        if (! str_starts_with($text, '/')) {
            $this->reply($replyToken, [['type' => 'text', 'text' => 'พิมพ์ /help ดูคำสั่งทั้งหมด']]);

            return;
        }

        $parts = explode(' ', $text, 2);
        $command = strtolower($parts[0]);
        $argument = $parts[1] ?? '';

        match ($command) {
            '/start' => $this->cmdStart($replyToken, $userId),
            '/help' => $this->cmdHelp($replyToken),
            '/link' => $this->cmdLink($replyToken, $userId, trim($argument)),
            '/devices' => $this->cmdDevices($replyToken, $userId),
            '/status' => $this->cmdStatus($replyToken, $userId),
            default => $this->reply($replyToken, [['type' => 'text', 'text' => "ไม่รู้จักคำสั่ง\nพิมพ์ /help ดูคำสั่งทั้งหมด"]]),
        };
    }

    private function handlePostback(array $event): void
    {
        $replyToken = $event['replyToken'] ?? '';
        $userId = $event['source']['userId'] ?? '';
        $data = $event['postback']['data'] ?? '';

        if (! str_starts_with($data, 'toggle:')) {
            return;
        }

        if (! $this->authorize($replyToken, $userId)) {
            return;
        }

        $deviceId = (int) str_replace('toggle:', '', $data);
        $device = Device::find($deviceId);

        if (! $device) {
            $this->reply($replyToken, [['type' => 'text', 'text' => 'ไม่พบอุปกรณ์']]);

            return;
        }

        $linkedUser = $this->findLinkedUser($userId);
        $device = app(DeviceService::class)->toggle($device, $linkedUser, 'line');

        $state = $device->is_on ? 'เปิด' : 'ปิด';
        $this->reply($replyToken, [['type' => 'text', 'text' => "✅ {$state} {$device->name} แล้ว"]]);
    }

    private function cmdStart(string $replyToken, string $userId): void
    {
        $user = $this->findLinkedUser($userId);
        $text = "🏫 Smart Classroom LINE Bot\n\nควบคุมอุปกรณ์ในห้องเรียนผ่าน LINE\n\n";

        if ($user) {
            $text .= "✅ เชื่อมบัญชีแล้ว: {$user->name}\nพิมพ์ /devices เพื่อควบคุมอุปกรณ์";
        } else {
            $text .= "1. ไปที่หน้า Profile บนเว็บ\n2. กดสร้างรหัสเชื่อมต่อ LINE\n3. พิมพ์ /link รหัส6หลัก";
        }

        $this->reply($replyToken, [['type' => 'text', 'text' => $text]]);
    }

    private function cmdHelp(string $replyToken): void
    {
        $this->reply($replyToken, [[
            'type' => 'text',
            'text' => implode("\n", [
                'คำสั่งที่ใช้ได้',
                '/start - เริ่มต้น',
                '/link รหัส - เชื่อมบัญชี',
                '/devices - ควบคุมอุปกรณ์',
                '/status - ดูสถานะทั้งหมด',
                '/help - วิธีใช้',
            ]),
        ]]);
    }

    private function cmdLink(string $replyToken, string $lineUserId, string $code): void
    {
        if (strlen($code) !== 6) {
            $this->reply($replyToken, [['type' => 'text', 'text' => '❌ รูปแบบไม่ถูกต้อง ใช้: /link 123456']]);

            return;
        }

        $userId = Cache::pull('line_link:' . $code);

        if (! $userId) {
            $this->reply($replyToken, [['type' => 'text', 'text' => '❌ รหัสหมดอายุหรือไม่ถูกต้อง กรุณาสร้างรหัสใหม่จากหน้า Profile']]);

            return;
        }

        $user = User::find($userId);

        if (! $user) {
            $this->reply($replyToken, [['type' => 'text', 'text' => '❌ ไม่พบผู้ใช้']]);

            return;
        }

        User::where('line_user_id', $lineUserId)->update(['line_user_id' => null]);
        $user->update(['line_user_id' => $lineUserId]);

        $this->reply($replyToken, [[
            'type' => 'text',
            'text' => "✅ เชื่อมบัญชีสำเร็จ!\nยินดีต้อนรับ {$user->name}\n\nพิมพ์ /devices เพื่อควบคุมอุปกรณ์",
        ]]);
    }

    private function cmdDevices(string $replyToken, string $userId): void
    {
        if (! $this->authorize($replyToken, $userId)) {
            return;
        }

        $devices = Device::with('room')->get();

        if ($devices->isEmpty()) {
            $this->reply($replyToken, [['type' => 'text', 'text' => 'ยังไม่มีอุปกรณ์ในระบบ']]);

            return;
        }

        $actions = $devices->take(4)->map(function (Device $device) {
            $state = $device->is_on ? '🟢' : '⚫';

            return [
                'type' => 'postback',
                'label' => mb_substr("{$state} {$device->name}", 0, 20),
                'data' => "toggle:{$device->id}",
                'displayText' => "สลับ {$device->name}",
            ];
        })->values()->all();

        $this->reply($replyToken, [[
            'type' => 'template',
            'altText' => 'เลือกอุปกรณ์เพื่อเปิด/ปิด',
            'template' => [
                'type' => 'buttons',
                'text' => '🔌 เลือกอุปกรณ์เพื่อเปิด/ปิด',
                'actions' => $actions,
            ],
        ]]);
    }

    private function cmdStatus(string $replyToken, string $userId): void
    {
        if (! $this->authorize($replyToken, $userId)) {
            return;
        }

        $devices = Device::with('room')->get();
        $lines = ["📊 สถานะอุปกรณ์", ''];

        foreach ($devices as $device) {
            $state = $device->is_on ? '🟢 เปิด' : '⚫ ปิด';
            $online = $device->is_online ? '' : ' (ออฟไลน์)';
            $room = $device->room?->name ?? '-';
            $lines[] = "{$device->name} [{$room}]: {$state}{$online}";
        }

        $this->reply($replyToken, [['type' => 'text', 'text' => implode("\n", $lines)]]);
    }

    private function authorize(string $replyToken, string $lineUserId): bool
    {
        $user = $this->findLinkedUser($lineUserId);

        if (! $user) {
            $this->reply($replyToken, [['type' => 'text', 'text' => '❌ กรุณาเชื่อมบัญชีก่อน ด้วยคำสั่ง /link รหัสจากหน้า Profile']]);

            return false;
        }

        if (! $user->isAdmin() && ! $user->isTeacher()) {
            $this->reply($replyToken, [['type' => 'text', 'text' => '❌ เฉพาะ Admin/อาจารย์เท่านั้นที่ควบคุมอุปกรณ์ได้']]);

            return false;
        }

        return true;
    }

    private function findLinkedUser(string $lineUserId): ?User
    {
        return User::where('line_user_id', $lineUserId)->first();
    }

    public static function generateLinkCode(int $userId): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put('line_link:' . $code, $userId, now()->addMinutes(10));

        return $code;
    }
}
