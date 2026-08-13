<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">ตรวจวัดสภาพแวดล้อม</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="p-4 bg-green-100 text-green-700 rounded">{{ session('success') }}</div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ($sensors as $sensor)
                    @php $reading = $sensor->latestReading; @endphp
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-sm text-gray-500">{{ $sensor->room?->name }}</p>
                        <h3 class="font-semibold text-lg">{{ $sensor->name }}</h3>
                        @if ($reading)
                            <p class="text-4xl font-bold mt-2 {{ $sensor->max_threshold && $reading->value > $sensor->max_threshold ? 'text-red-600' : 'text-indigo-600' }}">
                                {{ number_format($reading->value, 1) }}
                                <span class="text-lg">{{ $sensor->unit }}</span>
                            </p>
                            <p class="text-xs text-gray-400 mt-1">อัปเดต {{ $reading->recorded_at->diffForHumans() }}</p>
                        @else
                            <p class="text-gray-400 mt-4">รอข้อมูลจากเซนเซอร์</p>
                        @endif
                    </div>
                @endforeach
            </div>

            @if ($sensors->isNotEmpty())
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-semibold mb-4">กราฟข้อมูล {{ $hours }} ชั่วโมงล่าสุด</h3>
                    <canvas id="sensorChart" height="100"></canvas>
                </div>
            @endif

            @if (auth()->user()->isAdmin())
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-semibold mb-4">เพิ่มเซนเซอร์</h3>
                    <form method="POST" action="{{ route('admin.sensors.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @csrf
                        <input type="text" name="name" placeholder="ชื่อเซนเซอร์" required class="border rounded px-3 py-2">
                        <select name="type" required class="border rounded px-3 py-2">
                            <option value="temperature">อุณหภูมิ</option>
                            <option value="humidity">ความชื้น</option>
                            <option value="air_quality">คุณภาพอากาศ</option>
                        </select>
                        <input type="text" name="unit" placeholder="หน่วย (°C, %)" required class="border rounded px-3 py-2">
                        <select name="room_id" class="border rounded px-3 py-2">
                            <option value="">เลือกห้อง</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" step="0.1" name="max_threshold" placeholder="ค่าแจ้งเตือนสูงสุด" class="border rounded px-3 py-2">
                        <button type="submit" class="bg-indigo-600 text-white rounded px-4 py-2 hover:bg-indigo-700">เพิ่มเซนเซอร์</button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const readings = @json($readings);
        const sensors = @json($sensors->pluck('name', 'id'));
        const colors = ['#6366f1', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6'];
        const datasets = Object.entries(readings).map(([sensorId, data], i) => ({
            label: sensors[sensorId] || `Sensor ${sensorId}`,
            data: data.map(r => ({ x: r.recorded_at, y: parseFloat(r.value) })),
            borderColor: colors[i % colors.length],
            tension: 0.3,
            fill: false,
        }));
        if (datasets.length) {
            new Chart(document.getElementById('sensorChart'), {
                type: 'line',
                data: { datasets },
                options: {
                    responsive: true,
                    scales: {
                        x: { type: 'category', labels: datasets[0]?.data.map(d => new Date(d.x).toLocaleTimeString('th-TH', {hour:'2-digit', minute:'2-digit'})) },
                        y: { beginAtZero: false }
                    }
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
