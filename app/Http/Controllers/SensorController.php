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
        $hours = $request->get('hours', 24);

        $sensors = Sensor::with(['room', 'latestReading'])
            ->when($roomId, fn ($q) => $q->where('room_id', $roomId))
            ->get();

        $rooms = Room::where('is_active', true)->get();

        $readings = SensorReading::with('sensor')
            ->when($roomId, fn ($q) => $q->whereHas('sensor', fn ($sq) => $sq->where('room_id', $roomId)))
            ->where('recorded_at', '>=', Carbon::now()->subHours($hours))
            ->orderBy('recorded_at')
            ->get()
            ->groupBy('sensor_id');

        return view('sensors.index', compact('sensors', 'rooms', 'readings', 'roomId', 'hours'));
    }

    public function latest(Request $request)
    {
        $sensors = Sensor::with('latestReading')
            ->when($request->get('room_id'), fn ($q) => $q->where('room_id', $request->get('room_id')))
            ->get();

        return response()->json($sensors->map(function (Sensor $sensor) {
            $reading = $sensor->latestReading;
            $age = $reading?->recorded_at?->diffInSeconds(now());

            return [
                'id' => $sensor->id,
                'value' => $reading ? round((float) $reading->value, 1) : null,
                'unit' => $sensor->unit,
                'ago' => $age === null ? null : ($age <= 5 ? 'เมื่อกี้' : $reading->recorded_at->locale('th')->diffForHumans()),
            ];
        }));
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
