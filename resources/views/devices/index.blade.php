<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-cyan-400 mb-1">IoT Control</p>
                <h2 class="text-3xl font-black text-white">ควบคุม<span class="text-gradient">หลอดไฟ</span></h2>
            </div>
            <div class="flex flex-wrap gap-2">
                <form action="{{ route('admin.devices.turn-on-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 rounded-xl font-semibold text-white bg-emerald-500 hover:bg-emerald-600 shadow-md shadow-emerald-500/25 transition-all">
                        เปิดไฟรวม
                    </button>
                </form>
                <form action="{{ route('admin.devices.turn-off-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 rounded-xl font-semibold text-white bg-rose-500 hover:bg-rose-600 shadow-md shadow-rose-500/25 transition-all">
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

            <div class="app-card p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="font-bold text-white">หลอดไฟห้องเรียน</p>
                    <p class="text-sm text-slate-400 mt-1">เปิด/ปิดทีละดวง หรือใช้สวิตช์รวมด้านบน</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <form action="{{ route('admin.devices.turn-on-all') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-5 py-2.5 rounded-xl font-bold text-white bg-emerald-500 hover:bg-emerald-600 transition-all">
                            เปิดไฟรวม
                        </button>
                    </form>
                    <form action="{{ route('admin.devices.turn-off-all') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-5 py-2.5 rounded-xl font-bold text-white bg-rose-500 hover:bg-rose-600 transition-all">
                            ปิดไฟรวม
                        </button>
                    </form>
                </div>
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
                            <form action="{{ route('admin.devices.toggle', $device) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-6 py-2.5 rounded-xl font-semibold text-white transition-all {{ $device->is_on ? 'bg-rose-500 hover:bg-rose-600 shadow-md shadow-rose-500/25' : 'bg-emerald-500 hover:bg-emerald-600 shadow-md shadow-emerald-500/25' }}">
                                    {{ $device->is_on ? 'ปิด' : 'เปิด' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 app-card p-12 text-center text-slate-500">
                        ยังไม่มีหลอดไฟในระบบ —
                        <code class="text-cyan-400">php artisan db:seed --class=LightsDevicesSeeder</code>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
