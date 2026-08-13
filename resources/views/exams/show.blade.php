<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $exam->title }}</h2>
            @if ($exam->is_published && auth()->user()->isStudent())
                <a href="{{ route('admin.exams.take', $exam) }}" class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700">ทำข้อสอบ</a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="p-4 bg-green-100 text-green-700 rounded">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="p-4 bg-red-100 text-red-700 rounded">{{ session('error') }}</div>
            @endif

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-600 mb-2">{{ $exam->description }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $exam->questions->count() }} ข้อ · คอร์ส {{ $exam->course->title }}
                            @if ($exam->ai_generated)
                                · <span class="text-xs bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded">สร้างโดย AI</span>
                            @endif
                            @if ($exam->is_published)
                                · <span class="text-green-600">เปิดให้ทำแล้ว</span>
                            @else
                                · <span class="text-yellow-600">ยังไม่เปิด</span>
                            @endif
                        </p>
                    </div>
                    @if (auth()->user()->canManageCourse($exam->course))
                        <form method="POST" action="{{ route('admin.exams.publish', $exam) }}">
                            @csrf
                            <button type="submit" class="text-sm px-4 py-2 rounded {{ $exam->is_published ? 'bg-yellow-100 text-yellow-800' : 'bg-green-600 text-white' }}">
                                {{ $exam->is_published ? 'ปิดข้อสอบ' : 'เปิดให้นักเรียนทำ' }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            @if (auth()->user()->canManageCourse($exam->course))
                <div class="app-card p-6">
                    <h3 class="font-semibold text-lg text-white mb-1">สร้างคำถามเพิ่มด้วย AI</h3>
                    <p class="text-sm text-slate-400 mb-4">กดได้เรื่อยๆ · AI: {{ app(\App\Services\AiCurriculumService::class)->currentProvider() }}</p>
                    <form method="POST" action="{{ route('admin.ai.exam.append', $exam) }}" class="flex flex-wrap gap-3 items-end">
                        @csrf
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm mb-1">หัวข้อ / เนื้อหา</label>
                            <input type="text" name="topic" required placeholder="เช่น กฎของนิวตัน ข้อ 3"
                                   class="w-full border rounded px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm mb-1">จำนวน</label>
                            <select name="question_count" class="border rounded px-3 py-2 text-sm">
                                @foreach ([3, 5, 7, 10] as $n)
                                    <option value="{{ $n }}">{{ $n }} ข้อ</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded text-sm hover:bg-purple-700 whitespace-nowrap">
                            + สร้างเพิ่ม
                        </button>
                    </form>
                </div>

                <div class="bg-white rounded-lg shadow p-6" x-data="{ type: 'multiple_choice' }">
                    <h3 class="font-semibold text-lg mb-4">เพิ่มคำถาม</h3>
                    <form method="POST" action="{{ route('admin.exams.questions.store', $exam) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium mb-1">คำถาม</label>
                            <textarea name="question_text" rows="2" required class="w-full border rounded px-3 py-2">{{ old('question_text') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">ประเภท</label>
                            <select name="type" x-model="type" class="w-full border rounded px-3 py-2">
                                <option value="multiple_choice">ปรนัย (4 ตัวเลือก)</option>
                                <option value="analytical">วิเคราะห์</option>
                            </select>
                        </div>

                        <div x-show="type === 'multiple_choice'" class="space-y-2">
                            <label class="block text-sm font-medium">ตัวเลือก (เลือกข้อที่ถูก)</label>
                            @for ($i = 0; $i < 4; $i++)
                                <div class="flex items-center gap-2">
                                    <input type="radio" name="correct_option" value="{{ $i }}" {{ $i === 0 ? 'checked' : '' }} required>
                                    <input type="text" name="options[]" placeholder="ตัวเลือก {{ $i + 1 }}" class="flex-1 border rounded px-3 py-2">
                                </div>
                            @endfor
                        </div>

                        <div x-show="type === 'analytical'">
                            <label class="block text-sm font-medium mb-1">คำตอบที่ถูกต้อง</label>
                            <input type="text" name="correct_answer" class="w-full border rounded px-3 py-2" placeholder="คำตอบหลักสำหรับตรวจอัตโนมัติ">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">เฉลย/คำอธิบาย</label>
                            <textarea name="explanation" rows="2" class="w-full border rounded px-3 py-2" placeholder="แสดงหลังส่งข้อสอบ"></textarea>
                        </div>

                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">เพิ่มคำถาม</button>
                    </form>
                </div>
            @endif

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold text-lg mb-4">คำถามทั้งหมด ({{ $exam->questions->count() }})</h3>
                @forelse ($exam->questions as $index => $question)
                    <div class="border-b py-4 last:border-0">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="font-medium">{{ $index + 1 }}. {{ $question->question_text }}</p>
                                @if ($question->type === 'multiple_choice')
                                    <ul class="mt-2 space-y-1 text-sm text-gray-600">
                                        @foreach ($question->options as $opt)
                                            <li class="{{ $opt->is_correct ? 'text-green-600 font-medium' : '' }}">
                                                {{ $opt->is_correct ? '✓' : '○' }} {{ $opt->option_text }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-sm text-gray-500 mt-1">คำตอบ: {{ $question->correct_answer }}</p>
                                @endif
                                @if ($question->explanation)
                                    <p class="text-sm text-indigo-600 mt-2">เฉลย: {{ $question->explanation }}</p>
                                @endif
                            </div>
                            @if (auth()->user()->canManageCourse($exam->course))
                                <form action="{{ route('admin.exams.questions.destroy', $question) }}" method="POST" onsubmit="return confirm('ลบคำถามนี้?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 text-sm hover:underline ml-4">ลบ</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500">ยังไม่มีคำถาม — เพิ่มคำถามด้านบน</p>
                @endforelse
            </div>

            <a href="{{ route('admin.exams.index', $exam->course) }}" class="text-indigo-600 hover:underline">&larr; กลับรายการข้อสอบ</a>
        </div>
    </div>
</x-app-layout>
