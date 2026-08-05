<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Device;
use App\Models\Room;
use App\Models\Sensor;
use App\Models\SystemNotification;
use App\Services\AttendanceService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private AttendanceService $attendanceService,
        private NotificationService $notificationService,
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $today = Carbon::today();

        if ($user->isAdmin() || $user->isTeacher()) {
            $this->notificationService->checkAbsentStudents();
        }

        $attendanceStats = $this->attendanceService->todayStats();

        $recentAttendance = AttendanceRecord::with('user')
            ->whereDate('scanned_at', $today)
            ->where('type', 'in')
            ->latest('scanned_at')
            ->limit(10)
            ->get();

        $devices = Device::with('room')->classroomLights()->get();
        $sensors = Sensor::with(['room', 'latestReading'])->get();
        $rooms = Room::where('is_active', true)->get();

        $weeklyAttendance = AttendanceRecord::select(
            DB::raw('DATE(scanned_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('type', 'in')
            ->where('scanned_at', '>=', $today->copy()->subDays(6))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $studentData = null;
        if ($user->isStudent()) {
            $studentData = [
                'my_attendance' => AttendanceRecord::where('user_id', $user->id)
                    ->where('type', 'in')
                    ->latest('scanned_at')
                    ->limit(5)
                    ->get(),
                'enrolled_courses' => $user->enrollments()->with('course')->get(),
            ];
        }

        $teacherData = null;
        if ($user->isTeacher()) {
            $teacherData = [
                'courses' => $user->courses()->withCount('enrollments')->get(),
            ];
        }

        $recentAlerts = collect();
        $unreadAlertCount = 0;

        if ($user->isAdmin() || $user->isTeacher()) {
            $recentAlerts = SystemNotification::latest()->limit(5)->get();
            $unreadAlertCount = $this->notificationService->unreadCount();
        }

        return view('dashboard', compact(
            'attendanceStats',
            'recentAttendance',
            'devices',
            'sensors',
            'rooms',
            'weeklyAttendance',
            'studentData',
            'teacherData',
            'recentAlerts',
            'unreadAlertCount',
        ));
    }
}
