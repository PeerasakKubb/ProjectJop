<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class MqttService
{
    public function isEnabled(): bool
    {
        return (bool) config('services.mqtt.enabled');
    }

    public function publish(string $topic, array $payload): bool
    {
        $message = json_encode($payload);

        if (! $this->isEnabled()) {
            Log::info('MQTT (demo mode)', ['topic' => $topic, 'message' => $message]);

            return true;
        }

        $host = config('services.mqtt.host', 'localhost');
        $port = (int) config('services.mqtt.port', 1883);
        $username = config('services.mqtt.username');
        $password = config('services.mqtt.password');

        $command = ['mosquitto_pub', '-h', $host, '-p', (string) $port, '-t', $topic, '-m', $message];

        if ($username) {
            $command[] = '-u';
            $command[] = $username;
        }

        if ($password) {
            $command[] = '-P';
            $command[] = $password;
        }

        $result = Process::run($command);

        if (! $result->successful()) {
            Log::warning('MQTT publish failed', [
                'topic' => $topic,
                'error' => $result->errorOutput(),
            ]);
        }

        return $result->successful();
    }

    public function publishDeviceCommand(string $topic, string $command, int $deviceId): bool
    {
        return $this->publish($topic, [
            'device_id' => $deviceId,
            'command' => $command,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
