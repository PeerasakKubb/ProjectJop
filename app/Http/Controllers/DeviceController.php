<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Services\DeviceService;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function __construct(private DeviceService $deviceService) {}

    public function index()
    {
        $devices = Device::with('room')->classroomLights()->get();
        $anyOn = $devices->contains(fn (Device $d) => $d->is_on);
        $allOn = $devices->isNotEmpty() && $devices->every(fn (Device $d) => $d->is_on);

        return view('devices.index', compact('devices', 'anyOn', 'allOn'));
    }

    public function toggle(Request $request, Device $device)
    {
        abort_unless($device->type === 'light', 404);

        $validated = $request->validate([
            'source' => 'nullable|string|in:web,mobile,telegram,line',
        ]);

        $device = $this->deviceService->toggle(
            $device,
            auth()->user(),
            $validated['source'] ?? 'web'
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'device' => $device]);
        }

        return back()->with('success', ($device->is_on ? 'เปิด' : 'ปิด')." {$device->name} แล้ว");
    }

    public function turnOffAll(Request $request)
    {
        $count = $this->deviceService->turnOffAllLights(auth()->user(), 'web');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'turned_off' => $count]);
        }

        return back()->with(
            'success',
            $count > 0 ? "ปิดหลอดไฟรวมแล้ว ({$count} ดวง)" : 'ไม่มีไฟที่เปิดอยู่'
        );
    }

    public function turnOnAll(Request $request)
    {
        $count = $this->deviceService->turnOnAllLights(auth()->user(), 'web');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'turned_on' => $count]);
        }

        return back()->with(
            'success',
            $count > 0 ? "เปิดหลอดไฟรวมแล้ว ({$count} ดวง)" : 'ไฟเปิดครบทุกดวงแล้ว'
        );
    }
}
