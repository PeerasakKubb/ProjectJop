<?php

namespace Database\Seeders;

use App\Models\RfidCard;
use App\Models\RfidReader;
use App\Models\Room;
use App\Models\Sensor;
use App\Models\User;
use Illuminate\Database\Seeder;

class IotHubSeeder extends Seeder
{
    public function run(): void
    {
        $room = Room::query()->first();

        if (! $room) {
            $room = Room::create([
                'name' => 'ห้อง 101',
                'building' => 'A',
                'floor' => 1,
                'capacity' => 40,
            ]);
        }

        RfidReader::updateOrCreate(
            ['api_key' => 'demo-reader-key-12345'],
            [
                'name' => 'RFID Reader ประตูหน้า',
                'room_id' => $room->id,
                'location' => 'ประตูหน้า',
                'is_active' => true,
            ],
        );

        Sensor::updateOrCreate(
            ['api_key' => 'sensor-temp-key'],
            [
                'name' => 'เซนเซอร์อุณหภูมิ',
                'type' => 'temperature',
                'room_id' => $room->id,
                'unit' => '°C',
                'alert_enabled' => true,
                'max_threshold' => 35,
            ],
        );

        Sensor::updateOrCreate(
            ['api_key' => 'sensor-humidity-key'],
            [
                'name' => 'เซนเซอร์ความชื้น',
                'type' => 'humidity',
                'room_id' => $room->id,
                'unit' => '%',
                'alert_enabled' => false,
                'max_threshold' => null,
            ],
        );

        $students = User::query()
            ->where('role', 'student')
            ->orderBy('id')
            ->get();

        foreach ($students as $index => $student) {
            $uid = 'CARD'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);

            RfidCard::updateOrCreate(
                ['card_uid' => $uid],
                [
                    'user_id' => $student->id,
                    'is_active' => true,
                ],
            );
        }
    }
}
