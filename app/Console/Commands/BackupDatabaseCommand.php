<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'db:backup {--path= : Custom output filename inside storage/backups}';

    protected $description = 'Backup PostgreSQL database to storage/backups';

    public function handle(DatabaseBackupService $backup): int
    {
        $path = $backup->saveToStorage($this->option('path'));

        $this->info("Backup saved: {$path}");

        return self::SUCCESS;
    }
}
