<?php

namespace App\Http\Middleware;

use App\Models\RfidReader;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyReaderApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key');

        if (! $apiKey) {
            return response()->json(['message' => 'API key required'], 401);
        }

        $reader = RfidReader::where('api_key', $apiKey)->where('is_active', true)->first();

        if (! $reader) {
            return response()->json(['message' => 'Invalid API key'], 401);
        }

        $request->attributes->set('rfid_reader', $reader);

        return $next($request);
    }
}
