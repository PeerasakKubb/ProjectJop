<?php

use App\Models\Device;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('polls device command by api key', function () {
    $room = Room::create([
        'name' => 'ห้อง 101',
        'building' => 'A',
        'floor' => 1,
        'capacity' => 40,
    ]);

    $device = Device::create([
        'name' => 'ไฟหน้าห้อง',
        'type' => 'light',
        'room_id' => $room->id,
        'is_on' => true,
        'is_online' => false,
        'api_key' => 'device-light-key',
        'mqtt_topic' => 'classroom/101/light',
    ]);

    $response = $this->getJson('/api/devices/poll', [
        'X-API-Key' => 'device-light-key',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'device_id' => $device->id,
            'is_on' => true,
            'command' => 'on',
        ]);

    expect($device->fresh()->is_online)->toBeTrue();
});

it('polls all six LED commands for lights station', function () {
    $room = Room::create([
        'name' => 'ห้อง 101',
        'building' => 'A',
        'floor' => 1,
        'capacity' => 40,
    ]);

    foreach (range(1, 6) as $i) {
        Device::create([
            'name' => "หลอดไฟห้องเรียน {$i}",
            'type' => 'light',
            'room_id' => $room->id,
            'is_on' => $i === 1,
            'is_online' => false,
            'api_key' => "led-{$i}-key",
        ]);
    }

    $response = $this->getJson('/api/devices/poll-lights', [
        'X-API-Key' => 'lights-station-key',
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(6, 'devices');
});
