<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyBackupSecret
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('services.backup.secret');

        if (! $secret || $request->header('X-Backup-Key') !== $secret) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
