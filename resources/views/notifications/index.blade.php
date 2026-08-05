<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                การแจ้งเตือน
                @if ($unreadCount > 0)
                    <span class="ml-2 text-sm bg-red-500 text-white px-2 py-0.5 rounded-full">{{ $unreadCount }} ใหม่</span>
                @endif
            </h2>
            <div class="flex gap-2">
                @if ($unreadCount > 0)
                    <form action="{{ route('admin.notifications.read-all') }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded text-sm hover:bg-gray-700">
                            อ่านทั้งหมด
                        </button>
                    </form>
                @endif
                <form action="{{ route('admin.notifications.test') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700">
                        ทดสอบแจ้งเตือน
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white rounded-lg shadow divide-y">
                @forelse ($notifications as $notification)
                    <div class="p-5 flex gap-4 {{ $notification->is_read ? 'opacity-70' : 'bg-indigo-50/50' }}">
                        <div class="text-2xl">{{ $notification->icon() }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="text-xs px-2 py-0.5 rounded {{ $notification->badgeColor() }}">
                                        {{ $notification->type }}
                                    </span>
                                    <h3 class="font-semibold mt-1">{{ $notification->title }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">{{ $notification->message }}</p>
                                    <p class="text-xs text-gray-400 mt-2">
                                        {{ $notification->created_at->diffForHumans() }}
                                        @if ($notification->sent_telegram_at)
                                            · ส่ง Telegram แล้ว
                                        @endif
                                    </p>
                                </div>
                                @unless ($notification->is_read)
                                    <form action="{{ route('admin.notifications.read', $notification) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs text-indigo-600 hover:underline whitespace-nowrap">
                                            ทำเครื่องหมายอ่าน
                                        </button>
                                    </form>
                                @endunless
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center text-gray-500">
                        <p class="text-4xl mb-3">🔔</p>
                        <p>ยังไม่มีการแจ้งเตือน</p>
                        <p class="text-sm mt-2">ระบบจะแจ้งเมื่ออุณหภูมิสูง นักเรียนขาดเรียน หรืออุปกรณ์ออฟไลน์</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-4">{{ $notifications->links() }}</div>
        </div>
    </div>
</x-app-layout>
