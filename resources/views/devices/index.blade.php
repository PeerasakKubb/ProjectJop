<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-gold mb-1">IoT Control</p>
                <h2 class="text-3xl font-semibold text-white">ควบคุมหลอดไฟ</h2>
            </div>
            <div class="flex flex-wrap gap-2">
                <form method="POST" action="{{ route('admin.devices.turn-on-all') }}">
                    @csrf
                    <button type="submit" class="px-5 py-2 font-semibold text-white bg-emerald-700 hover:bg-emerald-800">
                        เปิดไฟรวม
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.devices.turn-off-all') }}">
                    @csrf
                    <button type="submit" class="px-5 py-2 font-semibold text-white bg-rose-800 hover:bg-rose-900">
                        ปิดไฟรวม
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="flash-success">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="rounded-xl border border-rose-500/40 bg-rose-500/10 p-4 text-rose-100 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="app-card p-5 space-y-2">
                <p class="font-bold text-white">หลอดไฟห้องเรียน</p>
                <p class="text-sm text-slate-400">
                    กดเปิด/ปิดแล้วสถานะบันทึกบนเซิร์ฟเวอร์ทันที — ESP32 ดึงจาก
                    <code class="text-cyan-400">{{ config('app.url') }}/api/devices/poll-lights</code>
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse ($devices as $device)
                    <div class="app-card p-6 {{ $device->is_on ? 'ring-2 ring-emerald-400/50' : '' }}">
                        <div class="flex justify-between items-start mb-5">
                            <div>
                                <h3 class="font-bold text-lg text-white">{{ $device->name }}</h3>
                                <p class="text-sm text-slate-500 mt-1">
                                    หลอดไฟ · {{ $device->room?->name ?? 'ไม่ระบุห้อง' }}
                                </p>
                            </div>
                            <span class="flex items-center gap-1.5 text-xs {{ $device->is_online ? 'text-emerald-600' : 'text-slate-400' }}">
                                <span class="w-2 h-2 rounded-full {{ $device->is_online ? 'bg-emerald-500 shadow-[0_0_6px_rgba(16,185,129,0.5)]' : 'bg-slate-300' }}"></span>
                                {{ $device->is_online ? 'ออนไลน์' : 'ออฟไลน์' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-3xl font-extrabold {{ $device->is_on ? 'text-emerald-600' : 'text-slate-300' }}">
                                {{ $device->is_on ? 'ON' : 'OFF' }}
                            </span>
                            <x-device-toggle-form :device="$device" />
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 app-card p-12 text-center text-slate-500">
                        ยังไม่มีหลอดไฟในระบบ
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
