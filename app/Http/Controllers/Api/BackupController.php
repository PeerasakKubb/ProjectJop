<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DatabaseBackupService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function download(DatabaseBackupService $backup): StreamedResponse
    {
        $filename = 'education-app-backup-'.now()->format('Y-m-d-His').'.sql';

        return response()->streamDownload(function () use ($backup) {
            echo $backup->createDump();
        }, $filename, [
            'Content-Type' => 'application/sql',
        ]);
    }
}
