<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class DatabaseBackupService
{
    public function createDump(): string
    {
        $url = $this->connectionUrl();

        if ($url !== null) {
            $result = Process::timeout(300)->run([
                'pg_dump',
                $url,
                '--no-owner',
                '--no-acl',
                '--clean',
                '--if-exists',
            ]);

            if ($result->successful() && trim($result->output()) !== '') {
                return $result->output();
            }
        }

        return $this->phpFallbackDump();
    }

    public function saveToStorage(?string $filename = null): string
    {
        $directory = storage_path('backups');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename ??= 'backup-'.now()->format('Y-m-d-His').'.sql';
        $path = $directory.DIRECTORY_SEPARATOR.$filename;

        file_put_contents($path, $this->createDump());

        return $path;
    }

    private function connectionUrl(): ?string
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}", []);

        if (! empty($config['url'])) {
            return $config['url'];
        }

        if (($config['driver'] ?? null) !== 'pgsql') {
            return null;
        }

        $user = rawurlencode((string) ($config['username'] ?? ''));
        $password = rawurlencode((string) ($config['password'] ?? ''));
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 5432;
        $database = $config['database'] ?? '';

        if ($database === '') {
            return null;
        }

        $auth = $user;
        if ($password !== '') {
            $auth .= ':'.$password;
        }

        return "postgresql://{$auth}@{$host}:{$port}/{$database}";
    }

    private function phpFallbackDump(): string
    {
        if (config('database.default') !== 'pgsql') {
            throw new RuntimeException('pg_dump unavailable and PHP fallback supports PostgreSQL only.');
        }

        $lines = [
            '-- Smart Classroom backup (PHP fallback)',
            '-- Generated at: '.now()->toDateTimeString(),
            'BEGIN;',
        ];

        $tables = DB::select(
            "SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename"
        );

        foreach ($tables as $table) {
            $name = $table->tablename;
            $lines[] = '';
            $lines[] = "-- Table: {$name}";

            $rows = DB::table($name)->get();

            if ($rows->isEmpty()) {
                continue;
            }

            $columns = array_keys((array) $rows->first());

            foreach ($rows as $row) {
                $values = array_map(function ($value) {
                    if ($value === null) {
                        return 'NULL';
                    }

                    if (is_bool($value)) {
                        return $value ? 'TRUE' : 'FALSE';
                    }

                    if (is_int($value) || is_float($value)) {
                        return (string) $value;
                    }

                    return "'".str_replace("'", "''", (string) $value)."'";
                }, array_values((array) $row));

                $columnList = implode(', ', array_map(fn ($column) => "\"{$column}\"", $columns));
                $valueList = implode(', ', $values);

                $lines[] = "INSERT INTO \"{$name}\" ({$columnList}) VALUES ({$valueList}) ON CONFLICT DO NOTHING;";
            }
        }

        $lines[] = 'COMMIT;';

        return implode(PHP_EOL, $lines).PHP_EOL;
    }
}
