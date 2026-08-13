<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-gold mb-1">RFID System</p>
                <h2 class="text-3xl font-semibold text-white">เช็คชื่อ</h2>
            </div>
            <a href="{{ route('admin.attendance.export', request()->only(['date', 'room_id'])) }}" class="btn-brand">
                Export CSV
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <x-stat-card label="เข้าเรียนวันนี้" :value="$stats['total']" color="brand" />
                <x-stat-card label="ตรงเวลา" :value="$stats['present']" color="green" />
                <x-stat-card label="มาสาย" :value="$stats['late']" color="amber" />
            </div>

            <div class="app-card p-6 mb-6">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1.5">วันที่</label>
                        <input type="date" name="date" value="{{ $date }}" class="input-modern px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1.5">ห้อง</label>
                        <select name="room_id" class="input-modern px-3 py-2">
                            <option value="">ทุกห้อง</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}" @selected($roomId == $room->id)>{{ $room->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn-brand">ค้นหา</button>
                </form>
            </div>

            <div class="app-card overflow-hidden">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-white/5 border-b border-white/10">
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">ชื่อ</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">ห้อง</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">เวลา</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($records as $record)
                            <tr class="hover:bg-white/5 transition-colors border-b border-white/5">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center text-xs font-bold">
                                            {{ mb_substr($record->user->name, 0, 1) }}
                                        </div>
                                        <span class="font-medium text-white">{{ $record->user->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-500">{{ $record->room?->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-slate-500">{{ $record->scanned_at->format('d/m/Y H:i:s') }}</td>
                                <td class="px-6 py-4">
                                    <span class="{{ $record->status === 'present' ? 'badge-success' : 'badge-warning' }}">
                                        {{ $record->status === 'present' ? 'ตรงเวลา' : 'มาสาย' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500">ไม่พบข้อมูลเช็คชื่อ</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t border-slate-100">{{ $records->withQueryString()->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
