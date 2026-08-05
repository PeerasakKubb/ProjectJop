<x-app-layout>
    <x-slot name="header">
        <x-module-header :title="$course->title" module="courses">
            <x-slot:actions>
                @if (auth()->user()->canManageCourse($course))
                    <a href="{{ route('admin.courses.edit', $course) }}" class="btn-outline text-sm">แก้ไขคอร์ส</a>
                @endif
                <a href="{{ route('courses.public.show', $course) }}" target="_blank" class="btn-outline text-sm">ดูหน้าบ้าน ↗</a>
            </x-slot:actions>
        </x-module-header>
    </x-slot>

    <div class="page-content max-w-4xl space-y-6">
        @if (session('success'))
            <div class="flash-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="flash-error">{{ session('error') }}</div>
        @endif

        @if (auth()->user()->canManageCourse($course))
            <x-page-card title="สร้างเนื้อหาด้วย AI">
                @php $aiService = app(\App\Services\AiCurriculumService::class); @endphp
                <p class="text-sm text-slate-400 mb-4">
                    วิชา: {{ $course->subject ?? $course->title }} · ระดับ: {{ $course->grade_level ?? 'ไม่ระบุ' }}
                    @if ($aiService->isConfigured())
                        · <span class="text-emerald-400">AI: {{ $aiService->currentProvider() }}</span>
                    @else
                        · <span class="text-rose-400">ยังไม่ได้ตั้ง AI key</span>
                    @endif
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <form method="POST" action="{{ route('admin.ai.lesson', $course) }}" class="p-4 rounded-xl bg-white/5 border border-white/10">
                        @csrf
                        <p class="font-semibold text-sm text-white mb-2">สร้างบทเรียน</p>
                        <input type="text" name="topic" placeholder="หัวข้อ เช่น แรงและการเคลื่อนที่" required
                               class="input-modern w-full px-3 py-2 text-sm mb-2">
                        <button type="submit" class="btn-brand w-full text-sm py-2">สร้างบทเรียนด้วย AI</button>
                    </form>
                    <form method="POST" action="{{ route('admin.ai.exam', $course) }}" class="p-4 rounded-xl bg-white/5 border border-white/10">
                        @csrf
                        <p class="font-semibold text-sm text-white mb-2">สร้างข้อสอบ</p>
                        <input type="text" name="topic" placeholder="หัวข้อข้อสอบ" required class="input-modern w-full px-3 py-2 text-sm mb-2">
                        <select name="question_count" class="input-modern w-full px-3 py-2 text-sm mb-2">
                            @foreach ([3, 5, 7, 10] as $n)
                                <option value="{{ $n }}">{{ $n }} ข้อ</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-brand w-full text-sm py-2">สร้างข้อสอบด้วย AI</button>
                    </form>
                </div>
            </x-page-card>
        @endif

        <div class="app-card p-6">
            <p class="text-slate-300 leading-relaxed mb-4">{{ $course->description }}</p>
            <div class="flex flex-wrap gap-4 text-sm text-slate-500 mb-4">
                <span>👨‍🏫 {{ $course->teacher->name }}</span>
                <span class="font-bold text-violet-400 text-base">{{ $course->price == 0 ? 'ฟรี' : number_format($course->price).' บาท' }}</span>
                @if (! $course->is_published)
                    <span class="badge-warning">ยังไม่เผยแพร่</span>
                @endif
            </div>

            @if (! auth()->user()->canManageCourse($course))
                @if ($isEnrolled)
                    <div class="flex items-center gap-3">
                        <span class="badge-success">ลงทะเบียนแล้ว</span>
                        <form action="{{ route('admin.enrollments.destroy', $course) }}" method="POST" onsubmit="return confirm('ยกเลิกการลงทะเบียน?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-rose-400 text-sm hover:underline">ยกเลิกลงทะเบียน</button>
                        </form>
                    </div>
                @else
                    <form action="{{ route('admin.enrollments.store', $course) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-brand">ลงทะเบียนเรียน</button>
                    </form>
                @endif
            @endif
        </div>

        <x-page-card title="บทเรียน">
            <x-slot:heading>
                <div class="flex justify-between items-center w-full">
                    <span>บทเรียนในคอร์ส</span>
                    @if (auth()->user()->canManageCourse($course))
                        <a href="{{ route('admin.lessons.create', $course) }}" class="btn-brand text-sm py-1.5 px-3">+ เพิ่มบทเรียน</a>
                    @endif
                </div>
            </x-slot:heading>
            <div class="space-y-1">
                @forelse ($course->lessons as $lesson)
                    <div class="flex justify-between items-center py-3 px-3 rounded-xl hover:bg-white/5">
                        <a href="{{ route('admin.lessons.show', $lesson) }}" class="font-medium text-white hover:text-cyan-300 transition-colors">
                            {{ $loop->iteration }}. {{ $lesson->title }}
                            @if ($lesson->ai_generated ?? false)
                                <span class="badge-brand text-[10px] ml-1">AI</span>
                            @endif
                        </a>
                        @if (auth()->user()->canManageCourse($course))
                            <form action="{{ route('admin.lessons.destroy', $lesson) }}" method="POST" onsubmit="return confirm('ลบบทเรียน?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-rose-400 text-xs hover:underline">ลบ</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-slate-500 text-sm py-4 text-center">ยังไม่มีบทเรียน</p>
                @endforelse
            </div>
        </x-page-card>

        <div class="app-card p-6 flex flex-wrap justify-between items-center gap-4">
            <div>
                <h3 class="font-bold text-white">ข้อสอบ</h3>
                <p class="text-sm text-slate-500">จัดการข้อสอบและดูความคืบหน้า</p>
            </div>
            <div class="flex gap-2">
                @if (auth()->user()->canManageCourse($course))
                    <a href="{{ route('admin.progress.index', $course) }}" class="btn-outline text-sm">ความคืบหน้า</a>
                @endif
                <a href="{{ route('admin.exams.index', $course) }}" class="btn-brand text-sm">จัดการข้อสอบ →</a>
            </div>
        </div>

        <a href="{{ route('admin.courses.index') }}" class="btn-ghost text-sm">← กลับรายการคอร์ส</a>
    </div>
</x-app-layout>
