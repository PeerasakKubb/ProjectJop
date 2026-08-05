<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\Room;
use Illuminate\Database\Seeder;

class LightsDevicesSeeder extends Seeder
{
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
            Device::updateOrCreate(
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
        }

        Device::query()
            ->whereNotIn('api_key', [
                'led-1-key', 'led-2-key', 'led-3-key',
                'led-4-key', 'led-5-key', 'led-6-key',
            ])
            ->delete();
    }
}
