<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Sensor;
use App\Models\SensorReading;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SensorController extends Controller
{
    public function index(Request $request)
    {
        $roomId = $request->get('room_id');
        $hours = (int) $request->get('hours', 24);

        $sensors = Sensor::with(['room', 'latestReading'])
            ->when($roomId, fn ($q) => $q->where('room_id', $roomId))
            ->get();

        $rooms = Room::where('is_active', true)->get();

        return view('sensors.index', compact('sensors', 'rooms', 'roomId', 'hours'));
    }

    public function latest(Request $request)
    {
        $sensors = Sensor::with(['room', 'latestReading'])
            ->when($request->get('room_id'), fn ($q) => $q->where('room_id', $request->get('room_id')))
            ->get();

        return response()->json($sensors->map(function (Sensor $sensor) {
            $reading = $sensor->latestReading;
            $age = $reading?->recorded_at?->diffInSeconds(now());

            return [
                'id' => $sensor->id,
                'name' => $sensor->name,
                'unit' => $sensor->unit,
                'value' => $reading ? (float) $reading->value : null,
                'ago' => $reading?->recorded_at?->locale('th')->diffForHumans(),
                'age_seconds' => $age,
                'online' => $age !== null && $age <= 90,
            ];
        }));
    }

    public function chart(Request $request)
    {
        $roomId = $request->get('room_id');
        $hours = min(max((int) $request->get('hours', 24), 1), 168);
        $since = Carbon::now()->subHours($hours);

        $sensors = Sensor::query()
            ->when($roomId, fn ($q) => $q->where('room_id', $roomId))
            ->get(['id', 'name']);

        $series = [];
        foreach ($sensors as $sensor) {
            $rows = SensorReading::query()
                ->where('sensor_id', $sensor->id)
                ->where('recorded_at', '>=', $since)
                ->orderByDesc('recorded_at')
                ->limit(120)
                ->get(['value', 'recorded_at']);

            $series[$sensor->id] = $rows->reverse()->values()->map(fn ($row) => [
                't' => $row->recorded_at->format('H:i'),
                'v' => (float) $row->value,
            ]);
        }

        return response()->json([
            'names' => $sensors->pluck('name', 'id'),
            'series' => $series,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:temperature,humidity,air_quality',
            'room_id' => 'nullable|exists:rooms,id',
            'unit' => 'required|string|max:20',
            'min_threshold' => 'nullable|numeric',
            'max_threshold' => 'nullable|numeric',
        ]);

        $validated['api_key'] = bin2hex(random_bytes(32));

        Sensor::create($validated);

        return back()->with('success', 'เพิ่มเซนเซอร์สำเร็จ');
    }
}
