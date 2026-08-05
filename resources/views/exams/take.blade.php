<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $exam->title }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.exams.submit', $exam) }}" class="space-y-6">
                @csrf
                @foreach ($exam->questions as $index => $question)
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="font-medium mb-4">{{ $index + 1 }}. {{ $question->question_text }}</p>

                        @if ($question->type === 'multiple_choice')
                            <div class="space-y-2">
                                @foreach ($question->options as $option)
                                    <label class="flex items-center gap-3 p-3 border rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" required>
                                        <span>{{ $option->option_text }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <textarea name="answers[{{ $question->id }}]" rows="4" required class="w-full border rounded px-3 py-2" placeholder="พิมพ์คำตอบ..."></textarea>
                        @endif
                    </div>
                @endforeach

                <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg font-medium hover:bg-indigo-700" onclick="return confirm('ยืนยันส่งข้อสอบ?')">
                    ส่งข้อสอบ
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
