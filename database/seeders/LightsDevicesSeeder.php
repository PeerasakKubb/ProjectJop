<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\Room;
use Illuminate\Database\Seeder;

class LightsDevicesSeeder extends Seeder
{
    /**
     * Ensure the 6 classroom LED devices exist without resetting on/off state
     * or deleting unrelated devices (deploy runs this on every boot).
     */
    public function run(): void
    {
        $room = Room::query()->first();

        $leds = [
            ['name' => 'หลอดไฟห้องเรียน 1', 'api_key' => 'led-1-key', 'mqtt_topic' => 'classroom/101/led1'],
            ['name' => 'หลอดไฟห้องเรียน 2', 'api_key' => 'led-2-key', 'mqtt_topic' => 'classroom/101/led2'],
            ['name' => 'หลอดไฟห้องเรียน 3', 'api_key' => 'led-3-key', 'mqtt_topic' => 'classroom/101/led3'],
            ['name' => 'หลอดไฟห้องเรียน 4', 'api_key' => 'led-4-key', 'mqtt_topic' => 'classroom/101/led4'],
            ['name' => 'หลอดไฟห้องเรียน 5', 'api_key' => 'led-5-key', 'mqtt_topic' => 'classroom/101/led5'],
            ['name' => 'หลอดไฟห้องเรียน 6', 'api_key' => 'led-6-key', 'mqtt_topic' => 'classroom/101/led6'],
        ];

        foreach ($leds as $led) {
            $device = Device::query()->firstOrCreate(
                ['api_key' => $led['api_key']],
                [
                    'name' => $led['name'],
                    'type' => 'light',
                    'room_id' => $room?->id,
                    'is_on' => false,
                    'is_online' => false,
                    'mqtt_topic' => $led['mqtt_topic'],
                ],
            );

            // Refresh metadata only — never wipe is_on / is_online on redeploy.
            $device->fill([
                'name' => $led['name'],
                'type' => 'light',
                'room_id' => $room?->id ?? $device->room_id,
                'mqtt_topic' => $led['mqtt_topic'],
            ])->save();
        }
    }
}