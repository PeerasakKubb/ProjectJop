<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $lesson->title }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6 mb-4">
                <p class="text-sm text-gray-500 mb-2">
                    คอร์ส: {{ $lesson->course->title }}
                    @if ($lesson->ai_generated)
                        · <span class="text-xs bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded">สร้างโดย AI</span>
                    @endif
                </p>

                @if ($lesson->video_url)
                    <div class="mb-6 aspect-video">
                        <iframe src="{{ $lesson->video_url }}" class="w-full h-full rounded" allowfullscreen></iframe>
                    </div>
                @endif

                <div class="prose max-w-none">
                    {!! nl2br(e($lesson->content)) !!}
                </div>
            </div>

            @if ($progress)
                <div class="bg-white rounded-lg shadow p-4" x-data="{ progress: {{ $progress->progress_percent }} }">
                    <div class="flex justify-between text-sm mb-2">
                        <span>ความคืบหน้าการทบทวน</span>
                        <span x-text="progress + '%'"></span>
                    </div>
                    <input type="range" min="0" max="100" x-model="progress"
                           @change="fetch('{{ route('admin.lessons.progress', $lesson) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }, body: JSON.stringify({ progress_percent: progress }) })"
                           class="w-full">
                </div>
            @endif

            <div class="mt-4">
                <a href="{{ route('admin.courses.show', $lesson->course) }}" class="text-indigo-600 hover:underline">&larr; กลับคอร์ส</a>
            </div>
        </div>
    </div>
</x-app-layout>
