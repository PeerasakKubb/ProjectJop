<x-app-layout>
    <x-slot name="header">
        <x-module-header module="dashboard">
            <x-slot:actions>
                <a href="{{ route('home') }}" class="btn-outline text-sm">หน้าบ้าน</a>
                <a href="{{ route('admin.architecture') }}" class="btn-outline text-sm">แผนภาพระบบ</a>
            </x-slot:actions>
        </x-module-header>
    </x-slot>

    <div class="page-content space-y-6">

        @if (auth()->user()->isAdmin())
            <div class="app-card p-5 border border-gold/30">
                <p class="text-xs font-bold uppercase tracking-widest text-gold mb-3">Admin · จัดการระบบ</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <a href="{{ route('admin.settings.index') }}" class="p-4 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-gold/40 transition-colors text-center">
                        <span class="text-sm font-semibold text-white">ตั้งค่าเว็บ</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="p-4 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-gold/40 transition-colors text-center">
                        <span class="text-sm font-semibold text-white">ผู้ใช้</span>
                    </a>
                    <a href="{{ route('admin.courses.index') }}" class="p-4 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-gold/40 transition-colors text-center">
                        <span class="text-sm font-semibold text-white">คอร์ส</span>
                    </a>
                    <a href="{{ route('home') }}" target="_blank" class="p-4 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-gold/40 transition-colors text-center">
                        <span class="text-sm font-semibold text-white">หน้าบ้าน</span>
                    </a>
                </div>
            </div>
        @endif

        <x-system-diagram compact :show-flow="false" class="app-card p-4" />

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card label="เข้าเรียนวันนี้" :value="$attendanceStats['total']" color="brand" />
            <x-stat-card label="มาตรงเวลา" :value="$attendanceStats['present']" color="green" />
            <x-stat-card label="มาสาย" :value="$attendanceStats['late']" color="amber" />
            <x-stat-card label="อุปกรณ์ออนไลน์" :value="$devices->where('is_online', true)->count() . '/' . $devices->count()" color="blue" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-page-card title="เช็คชื่อล่าสุดวันนี้" :action="route('admin.attendance.index')">
                <div class="space-y-1" id="recent-attendance">
                    @forelse ($recentAttendance as $record)
                        <div class="flex justify-between items-center py-3 px-3 hover:bg-white/5 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="gov-initials w-9 h-9 flex items-center justify-center text-sm font-bold">
                                    {{ mb_substr($record->user->name, 0, 1) }}
                                </div>
                                <span class="font-medium text-white">{{ $record->user->name }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-slate-500">{{ $record->scanned_at->format('H:i') }}</span>
                                <span class="{{ $record->status === 'present' ? 'badge-success' : 'badge-warning' }}">
                                    {{ $record->status === 'present' ? 'ตรงเวลา' : 'สาย' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm py-8 text-center">ยังไม่มีการเช็คชื่อวันนี้</p>
                    @endforelse
                </div>
            </x-page-card>

            <x-page-card title="สถิติเข้าเรียน 7 วัน">
                <canvas id="attendanceChart" height="200"></canvas>
            </x-page-card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-page-card title="ควบคุมอุปกรณ์" :action="route('admin.devices.index')" actionLabel="จัดการ">
                <div class="grid grid-cols-2 gap-3">
                    @forelse ($devices as $device)
                        <div data-device-card class="p-4 border transition-colors {{ $device->is_on ? 'border-emerald-500/40 bg-emerald-500/10' : 'border-white/10 bg-white/5' }}">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <p class="font-semibold text-sm text-white">{{ $device->name }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $device->room?->name ?? 'ไม่ระบุห้อง' }}</p>
                                </div>
                                <span class="w-2.5 h-2.5 rounded-full {{ $device->is_online ? 'bg-emerald-400' : 'bg-slate-600' }}"></span>
                            </div>
                            <x-device-toggle-form
                                :device="$device"
                                button-class="w-full text-sm py-2 font-medium text-white transition-colors"
                            />
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm col-span-2 text-center py-6">ยังไม่มีอุปกรณ์</p>
                    @endforelse
                </div>
            </x-page-card>

            <x-page-card title="สภาพแวดล้อมห้องเรียน" :action="route('admin.sensors.index')" actionLabel="ดูกราฟ">
                <div class="space-y-2">
                    @forelse ($sensors as $sensor)
                        @php $reading = $sensor->latestReading; @endphp
                        <div class="flex justify-between items-center p-4 bg-white/5 border border-white/10">
                            <div>
                                <p class="font-semibold text-sm text-white">{{ $sensor->name }}</p>
                                <p class="text-xs text-slate-500">{{ $sensor->room?->name }} · {{ $sensor->type }}</p>
                            </div>
                            <div class="text-right">
                                @if ($reading)
                                    <p class="text-xl font-bold {{ $sensor->max_threshold && $reading->value > $sensor->max_threshold ? 'text-rose-400' : 'text-gold' }}">
                                        {{ number_format($reading->value, 1) }} <span class="text-sm font-normal">{{ $sensor->unit }}</span>
                                    </p>
                                    <p class="text-xs text-slate-400">{{ $reading->recorded_at->diffForHumans() }}</p>
                                @else
                                    <p class="text-slate-400 text-sm">ไม่มีข้อมูล</p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm text-center py-6">ยังไม่มีเซนเซอร์</p>
                    @endforelse
                </div>
            </x-page-card>
        </div>

        @if (auth()->user()->isAdmin() || auth()->user()->isTeacher())
            <x-page-card :action="route('admin.notifications.index')">
                <x-slot:heading>
                    <span class="flex items-center gap-2">
                        การแจ้งเตือนล่าสุด
                        @if ($unreadAlertCount > 0)
                            <span class="badge-danger">{{ $unreadAlertCount }} ใหม่</span>
                        @endif
                    </span>
                </x-slot:heading>
                <div class="space-y-2">
                    @forelse ($recentAlerts as $alert)
                        <div class="flex gap-3 p-4 {{ $alert->is_read ? 'bg-white/5' : 'border border-gold/30 bg-white/[0.03]' }}">
                            <div class="flex-1 min-w-0">
                                <span class="{{ $alert->badgeColor() }}">{{ $alert->typeLabel() }}</span>
                                <p class="font-semibold text-sm text-white mt-2">{{ $alert->title }}</p>
                                <p class="text-xs text-slate-500 truncate mt-0.5">{{ $alert->message }}</p>
                            </div>
                            <span class="text-xs text-slate-400 whitespace-nowrap">{{ $alert->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm text-center py-6">ไม่มีการแจ้งเตือน</p>
                    @endforelse
                </div>
            </x-page-card>
        @endif

        @if ($teacherData)
            <x-page-card title="คอร์สของฉัน">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach ($teacherData['courses'] as $course)
                        <a href="{{ route('admin.courses.show', $course) }}" class="group p-5 border border-white/10 bg-white/5 hover:border-gold/40 transition-colors">
                            <p class="font-semibold text-white group-hover:text-gold transition-colors">{{ $course->title }}</p>
                            <p class="text-sm text-slate-500 mt-1">{{ $course->enrollments_count }} นักเรียน</p>
                        </a>
                    @endforeach
                </div>
            </x-page-card>
        @endif

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('attendanceChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($weeklyAttendance->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))) !!},
                    datasets: [{
                        label: 'จำนวนคนเข้าเรียน',
                        data: {!! json_encode($weeklyAttendance->pluck('count')) !!},
                        backgroundColor: 'rgba(196, 163, 90, 0.35)',
                        borderColor: '#c4a35a',
                        borderWidth: 1,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#94a3b8' },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, color: '#94a3b8' },
                            grid: { color: 'rgba(255,255,255,0.06)' },
                        }
                    }
                }
            });
        }

        setInterval(() => {
            fetch('{{ route('admin.attendance.today') }}')
                .then(r => r.json())
                .then(data => {
                    const container = document.getElementById('recent-attendance');
                    if (!data.length) return;
                    container.innerHTML = data.slice(0, 10).map(r => `
                        <div class="flex justify-between items-center py-3 px-3 hover:bg-white/5 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="gov-initials w-9 h-9 flex items-center justify-center text-sm font-bold">${r.user.name.charAt(0)}</div>
                                <span class="font-medium text-white">${r.user.name}</span>
                            </div>
                            <span class="text-sm text-slate-500">${new Date(r.scanned_at).toLocaleTimeString('th-TH', {hour:'2-digit', minute:'2-digit'})}</span>
                        </div>
                    `).join('');
                });
        }, 10000);
    </script>
    @endpush
</x-app-layout>
