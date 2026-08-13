<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="font-semibold text-xl text-white leading-tight">
                การแจ้งเตือน
                @if ($unreadCount > 0)
                    <span class="ml-2 badge-danger">{{ $unreadCount }} ใหม่</span>
                @endif
            </h2>
            <div class="flex gap-2">
                @if ($unreadCount > 0)
                    <form action="{{ route('admin.notifications.read-all') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-outline text-sm">อ่านทั้งหมด</button>
                    </form>
                @endif
                <form action="{{ route('admin.notifications.test') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-brand text-sm">ทดสอบแจ้งเตือน</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="page-content">
        @if (session('success'))
            <div class="flash-success mb-4">{{ session('success') }}</div>
        @endif

        <div class="app-card divide-y divide-white/10">
            @forelse ($notifications as $notification)
                <div class="p-5 {{ $notification->is_read ? '' : 'bg-white/[0.03]' }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <span class="{{ $notification->badgeColor() }}">{{ $notification->typeLabel() }}</span>
                            <h3 class="font-semibold text-white mt-2">{{ $notification->title }}</h3>
                            <p class="text-sm text-slate-400 mt-1">{{ $notification->message }}</p>
                            <p class="text-xs text-slate-500 mt-2">
                                {{ $notification->created_at->diffForHumans() }}
                                @if ($notification->sent_telegram_at)
                                    · ส่ง Telegram แล้ว
                                @endif
                            </p>
                        </div>
                        @unless ($notification->is_read)
                            <form action="{{ route('admin.notifications.read', $notification) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-xs text-gold hover:underline whitespace-nowrap">
                                    ทำเครื่องหมายอ่าน
                                </button>
                            </form>
                        @endunless
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-slate-500">
                    <p>ยังไม่มีการแจ้งเตือน</p>
                    <p class="text-sm mt-2">ระบบจะแจ้งเมื่ออุณหภูมิสูง นักเรียนขาดเรียน หรืออุปกรณ์ออฟไลน์</p>
                </div>
            @endforelse
        </div>

        <div class="mt-4">{{ $notifications->links() }}</div>
    </div>
</x-app-layout>
