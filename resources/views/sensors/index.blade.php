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
                    <p id="sensorChartStatus" class="text-sm text-gray-400 mb-2">กำลังโหลดกราฟ…</p>
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

    @if ($sensors->isNotEmpty())
    @push('scripts')
    <script>
        (function () {
            const status = document.getElementById('sensorChartStatus');
            const canvas = document.getElementById('sensorChart');
            const chartUrl = @json(route('admin.sensors.chart', array_filter(['room_id' => $roomId, 'hours' => $hours])));
            const colors = ['#6366f1', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6'];

            function drawChart(payload) {
                const datasets = Object.entries(payload.series || {}).map(([id, points], i) => ({
                    label: payload.names[id] || ('Sensor ' + id),
                    data: (points || []).map((p) => p.v),
                    borderColor: colors[i % colors.length],
                    tension: 0.3,
                    fill: false,
                    pointRadius: 0,
                }));
                const labels = Object.values(payload.series || [])[0]?.map((p) => p.t) || [];
                if (!datasets.length || !labels.length) {
                    if (status) status.textContent = 'ยังไม่มีข้อมูลกราฟในช่วงเวลานี้';
                    return;
                }
                if (status) status.remove();
                new Chart(canvas, {
                    type: 'line',
                    data: { labels, datasets },
                    options: {
                        responsive: true,
                        animation: false,
                        plugins: { legend: { labels: { color: '#64748b' } } },
                        scales: {
                            x: { ticks: { maxTicksLimit: 12, color: '#94a3b8' } },
                            y: { beginAtZero: false, ticks: { color: '#94a3b8' } }
                        }
                    }
                });
            }

            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';
            script.onload = function () {
                fetch(chartUrl, { headers: { 'Accept': 'application/json' } })
                    .then((r) => r.json())
                    .then(drawChart)
                    .catch(function () {
                        if (status) status.textContent = 'โหลดกราฟไม่สำเร็จ';
                    });
            };
            script.onerror = function () {
                if (status) status.textContent = 'โหลดคลังกราฟไม่สำเร็จ';
            };
            document.head.appendChild(script);
        })();
    </script>
    @endpush
    @endif
</x-app-layout>
