<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LineWebhookController extends Controller
{
    public function __construct(private LineService $line) {}

    public function handle(Request $request): JsonResponse
    {
        if (! $this->line->isConfigured()) {
            return response()->json(['ok' => false], 503);
        }

        $signature = $request->header('X-Line-Signature');

        if (! $this->line->verifySignature($request->getContent(), $signature)) {
            return response()->json(['ok' => false], 403);
        }

        $this->line->handleWebhook($request->input('events', []));

        return response()->json(['ok' => true]);
    }
}
