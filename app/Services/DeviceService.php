<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DeviceCommand;
use App\Models\User;

class DeviceService
{
    public function __construct(
        private NotificationService $notifications,
        private MqttService $mqtt,
    ) {}

    public function setState(Device $device, bool $isOn, ?User $user = null, string $source = 'web'): Device
    {
        $wasOn = $device->is_on;
        $device->update(['is_on' => $isOn]);

        if ($wasOn !== $isOn) {
            $this->notifications->deviceStatusAlert($device->name, $isOn, $device->room?->name);

            if ($device->mqtt_topic) {
                $this->mqtt->publishDeviceCommand(
                    $device->mqtt_topic,
                    $isOn ? 'on' : 'off',
                    $device->id,
                );
            }
        }

        DeviceCommand::create([
            'device_id' => $device->id,
            'user_id' => $user?->id,
            'command' => $isOn ? 'on' : 'off',
            'source' => $source,
            'executed_at' => now(),
        ]);

        return $device->fresh();
    }

    public function toggle(Device $device, ?User $user = null, string $source = 'web'): Device
    {
        return $this->setState($device, ! $device->is_on, $user, $source);
    }

    public function turnOffAllLights(?User $user = null, string $source = 'web'): int
    {
        return $this->setAllLights(false, $user, $source);
    }

    public function turnOnAllLights(?User $user = null, string $source = 'web'): int
    {
        return $this->setAllLights(true, $user, $source);
    }

    public function setAllLights(bool $isOn, ?User $user = null, string $source = 'web'): int
    {
        $lights = Device::classroomLights()
            ->where('is_on', '!=', $isOn)
            ->get();

        foreach ($lights as $device) {
            $device->update(['is_on' => $isOn]);

            if ($device->mqtt_topic) {
                $this->mqtt->publishDeviceCommand(
                    $device->mqtt_topic,
                    $isOn ? 'on' : 'off',
                    $device->id,
                );
            }

            DeviceCommand::create([
                'device_id' => $device->id,
                'user_id' => $user?->id,
                'command' => $isOn ? 'on' : 'off',
                'source' => $source,
                'executed_at' => now(),
            ]);
        }

        $count = $lights->count();

        if ($count > 0) {
            $this->notifications->deviceStatusAlert(
                'หลอดไฟห้องเรียนทั้งหมด',
                $isOn,
                $lights->first()->room?->name,
            );
        }

        return $count;
    }
}
