<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramWebhookController extends Controller
{
    public function __construct(private TelegramService $telegram) {}

    public function handle(Request $request): JsonResponse
    {
        if (! $this->telegram->isConfigured()) {
            return response()->json(['ok' => false], 503);
        }

        $secret = config('services.telegram.webhook_secret');
        if ($secret && $request->header('X-Telegram-Bot-Api-Secret-Token') !== $secret) {
            return response()->json(['ok' => false], 403);
        }

        $this->telegram->handleUpdate($request->all());

        return response()->json(['ok' => true]);
    }
}
