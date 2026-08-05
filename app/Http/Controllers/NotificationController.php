<?php

namespace App\Http\Controllers;

use App\Models\SystemNotification;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    public function index()
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->isTeacher(), 403);

        $notifications = SystemNotification::latest()->paginate(20);
        $unreadCount = $this->notifications->unreadCount();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markRead(SystemNotification $notification)
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->isTeacher(), 403);

        $notification->update(['is_read' => true]);

        return back()->with('success', 'ทำเครื่องหมายอ่านแล้ว');
    }

    public function markAllRead()
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->isTeacher(), 403);

        SystemNotification::where('is_read', false)->update(['is_read' => true]);

        return back()->with('success', 'อ่านการแจ้งเตือนทั้งหมดแล้ว');
    }

    public function test(Request $request)
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->isTeacher(), 403);

        $this->notifications->alert(
            type: 'temperature',
            title: 'ทดสอบแจ้งเตือน',
            message: 'ระบบแจ้งเตือนทำงานปกติ — ส่งจากปุ่มทดสอบ',
            sendTelegram: true,
            cooldownKey: null,
        );

        return back()->with('success', 'ส่งการแจ้งเตือนทดสอบแล้ว (เว็บ + Telegram)');
    }
}
