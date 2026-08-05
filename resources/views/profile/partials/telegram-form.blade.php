<section>
    <header class="mb-4">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">เชื่อมต่อ Telegram Bot</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            ควบคุมอุปกรณ์ในห้องเรียนผ่าน Telegram
            @if (config('services.telegram.bot_username'))
                — ค้นหา <strong>@{{ config('services.telegram.bot_username') }}</strong>
            @endif
        </p>
    </header>

    @if (session('telegram_code'))
        <div class="mb-4 p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
            <p class="text-sm text-indigo-800 mb-2">รหัสเชื่อมต่อ (หมดอายุใน 10 นาที):</p>
            <p class="text-3xl font-mono font-bold text-indigo-600 tracking-widest">{{ session('telegram_code') }}</p>
            <p class="text-sm text-indigo-600 mt-2">
                เปิด Telegram แล้วพิมพ์: <code class="bg-white px-2 py-0.5 rounded">/link {{ session('telegram_code') }}</code>
            </p>
        </div>
    @endif

    @if (session('status') === 'telegram-unlinked')
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded text-sm">ยกเลิกการเชื่อม Telegram แล้ว</div>
    @endif

    @if ($user->telegram_chat_id)
        <div class="flex items-center justify-between p-4 bg-green-50 border border-green-200 rounded-lg">
            <div>
                <p class="text-green-800 font-medium">✅ เชื่อมต่อ Telegram แล้ว</p>
                <p class="text-sm text-green-600">พิมพ์ /devices ใน Bot เพื่อควบคุมอุปกรณ์</p>
            </div>
            <form method="POST" action="{{ route('admin.profile.telegram.unlink') }}">
                @csrf @method('DELETE')
                <button type="submit" class="text-sm text-red-600 hover:underline">ยกเลิกการเชื่อม</button>
            </form>
        </div>
    @else
        <form method="POST" action="{{ route('admin.profile.telegram.code') }}">
            @csrf
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded text-sm hover:bg-blue-600">
                สร้างรหัสเชื่อมต่อ Telegram
            </button>
        </form>
    @endif
</section>
