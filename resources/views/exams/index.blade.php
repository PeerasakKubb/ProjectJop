<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">ข้อสอบ — {{ $course->title }}</h2>
            @if (auth()->user()->canManageCourse($course))
                <a href="{{ route('admin.exams.create', $course) }}" class="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700">+ สร้างข้อสอบ</a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (auth()->user()->canManageCourse($course))
                <div class="app-card p-6">
                    <h3 class="font-semibold text-white mb-3">สร้างข้อสอบด้วย AI</h3>
                    <form method="POST" action="{{ route('admin.ai.exam', $course) }}" class="flex flex-wrap gap-3 items-end">
                        @csrf
                        <div class="flex-1 min-w-[200px]">
                            <input type="text" name="topic" placeholder="หัวข้อ เช่น กฎของนิวตัน" required
                                   class="w-full border rounded px-3 py-2 text-sm">
                        </div>
                        <select name="question_count" class="border rounded px-3 py-2 text-sm">
                            @foreach ([3, 5, 7, 10] as $n)
                                <option value="{{ $n }}">{{ $n }} ข้อ</option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded text-sm hover:bg-purple-700">
                            สร้างข้อสอบด้วย AI
                        </button>
                    </form>
                </div>
            @endif

            @forelse ($exams as $exam)
                <div class="bg-white rounded-lg shadow p-6 flex justify-between items-center">
                    <div>
                        <h3 class="font-semibold text-lg">{{ $exam->title }}</h3>
                        <p class="text-sm text-gray-500">{{ $exam->questions_count }} ข้อ · {{ $exam->type === 'multiple_choice' ? 'ปรนัย' : 'วิเคราะห์' }}</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.exams.show', $exam) }}" class="px-4 py-2 border rounded text-sm hover:bg-gray-50">ดู</a>
                        @if ($exam->is_published && auth()->user()->isStudent())
                            <a href="{{ route('admin.exams.take', $exam) }}" class="px-4 py-2 bg-green-600 text-white rounded text-sm hover:bg-green-700">ทำข้อสอบ</a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow p-12 text-center text-gray-500">ยังไม่มีข้อสอบ</div>
            @endforelse
            <a href="{{ route('admin.courses.show', $course) }}" class="text-indigo-600 hover:underline">&larr; กลับคอร์ส</a>
        </div>
    </div>
</x-app-layout>
