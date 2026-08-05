<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ความคืบหน้าการเรียน — {{ $course->title }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('admin.courses.show', $course) }}" class="text-indigo-600 hover:underline text-sm">
                    &larr; กลับไปคอร์ส
                </a>
            </div>

            @if ($students->isEmpty())
                <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                    ยังไม่มีนักเรียนลงทะเบียนในคอร์สนี้
                </div>
            @else
                <div class="bg-white rounded-lg shadow overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">นักเรียน</th>
                                @foreach ($course->lessons as $lesson)
                                    <th class="px-3 py-3 text-center font-medium text-gray-600 max-w-[120px]">
                                        <span class="line-clamp-2" title="{{ $lesson->title }}">
                                            {{ Str::limit($lesson->title, 20) }}
                                        </span>
                                    </th>
                                @endforeach
                                <th class="px-4 py-3 text-center font-medium text-gray-600">เฉลี่ย</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($students as $row)
                                <tr>
                                    <td class="px-4 py-3 font-medium whitespace-nowrap">{{ $row['user']->name }}</td>
                                    @foreach ($row['lessons'] as $lessonRow)
                                        @php $pct = $lessonRow['percent']; @endphp
                                        <td class="px-3 py-3 text-center">
                                            <div class="flex flex-col items-center gap-1">
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="h-2 rounded-full {{ $pct >= 100 ? 'bg-green-500' : ($pct >= 50 ? 'bg-indigo-500' : 'bg-yellow-400') }}"
                                                         style="width: {{ min(100, $pct) }}%"></div>
                                                </div>
                                                <span class="text-xs text-gray-500">{{ $pct }}%</span>
                                            </div>
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-3 text-center">
                                        <span class="font-bold {{ $row['average'] >= 80 ? 'text-green-600' : ($row['average'] >= 50 ? 'text-indigo-600' : 'text-yellow-600') }}">
                                            {{ $row['average'] }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
