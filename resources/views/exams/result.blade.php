<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">ผลข้อสอบ — {{ $attempt->exam->title }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <p class="text-5xl font-bold {{ $attempt->score >= 50 ? 'text-green-600' : 'text-red-600' }}">{{ $attempt->score }}%</p>
                <p class="text-gray-500 mt-2">คะแนนที่ได้</p>
            </div>

            @foreach ($attempt->exam->questions as $index => $question)
                @php $answer = $attempt->answers->firstWhere('question_id', $question->id); @endphp
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="font-medium mb-2">{{ $index + 1 }}. {{ $question->question_text }}</p>
                    <p class="text-sm mb-2">คำตอบ: {{ $answer?->answer_text ?? '-' }}</p>
                    <p class="text-sm {{ $answer?->is_correct ? 'text-green-600' : 'text-red-600' }}">
                        {{ $answer?->is_correct ? '✓ ถูกต้อง' : '✗ ไม่ถูกต้อง' }}
                    </p>
                    @if ($answer?->ai_feedback)
                        <p class="text-sm text-slate-300 mt-2 bg-white/5 p-3 border border-white/10">AI: {{ $answer->ai_feedback }}</p>
                    @endif
                    @if ($question->explanation)
                        <p class="text-sm text-gray-500 mt-2 bg-gray-50 p-3 rounded">เฉลย: {{ $question->explanation }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
