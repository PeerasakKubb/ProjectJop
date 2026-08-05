<section>
    <header class="mb-4">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">เชื่อมต่อ LINE Bot</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            ควบคุมอุปกรณ์และรับแจ้งเตือนผ่าน LINE Official Account
        </p>
    </header>

    @if (session('line_code'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-sm text-green-800 mb-2">รหัสเชื่อมต่อ LINE (หมดอายุใน 10 นาที):</p>
            <p class="text-3xl font-mono font-bold text-green-600 tracking-widest">{{ session('line_code') }}</p>
            <p class="text-sm text-green-600 mt-2">
                เปิด LINE แล้วพิมพ์: <code class="bg-white px-2 py-0.5 rounded">/link {{ session('line_code') }}</code>
            </p>
        </div>
    @endif

    @if (session('status') === 'line-unlinked')
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded text-sm">ยกเลิกการเชื่อม LINE แล้ว</div>
    @endif

    @if ($user->line_user_id)
        <div class="flex items-center justify-between p-4 bg-green-50 border border-green-200 rounded-lg">
            <div>
                <p class="text-green-800 font-medium">✅ เชื่อมต่อ LINE แล้ว</p>
                <p class="text-sm text-green-600">พิมพ์ /devices ใน LINE เพื่อควบคุมอุปกรณ์</p>
            </div>
            <form method="POST" action="{{ route('admin.profile.line.unlink') }}">
                @csrf @method('DELETE')
                <button type="submit" class="text-sm text-red-600 hover:underline">ยกเลิกการเชื่อม</button>
            </form>
        </div>
    @else
        <form method="POST" action="{{ route('admin.profile.line.code') }}">
            @csrf
            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded text-sm hover:bg-green-600">
                สร้างรหัสเชื่อมต่อ LINE
            </button>
        </form>
        <p class="text-xs text-gray-500 mt-2">
            ต้องสร้าง LINE Official Account และตั้ง Webhook ก่อน (ดูใน firmware/README.md)
        </p>
    @endif
</section>
