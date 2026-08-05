<x-app-layout>
    <x-slot name="header">
        <x-module-header module="courses">
            <x-slot:actions>
                @if (auth()->user()->isAdmin() || auth()->user()->isTeacher())
                    <a href="{{ route('admin.courses.create') }}" class="btn-brand">+ สร้างคอร์สใหม่</a>
                @endif
            </x-slot:actions>
        </x-module-header>
    </x-slot>

    <div class="page-content">
        @if (session('success'))
            <div class="flash-success mb-6">{{ session('success') }}</div>
        @endif

        <p class="text-slate-500 mb-6">พบ <span class="font-bold text-cyan-400">{{ $courses->count() }}</span> คอร์ส</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse ($courses as $course)
                <div class="app-card group overflow-hidden flex flex-col hover:-translate-y-1 transition-all duration-300">
                    <div class="h-1" style="background: linear-gradient(90deg, #7c3aed, #22d3ee, #f472b6);"></div>
                    <div class="p-6 flex flex-col flex-1">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <h3 class="text-lg font-bold text-white group-hover:text-cyan-300 transition-colors">{{ $course->title }}</h3>
                            @if (! $course->is_published)
                                <span class="badge-warning text-[10px] shrink-0">Draft</span>
                            @endif
                        </div>
                        <p class="text-slate-400 text-sm mb-4 flex-1 leading-relaxed">{{ Str::limit($course->description, 100) }}</p>
                        <div class="flex justify-between items-center mb-4 text-sm">
                            <span class="text-slate-500">{{ $course->teacher->name }}</span>
                            <span class="font-bold text-violet-400">
                                {{ $course->price == 0 ? 'ฟรี' : number_format($course->price) . ' บาท' }}
                            </span>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.courses.show', $course) }}" class="flex-1 text-center py-2.5 rounded-xl text-sm font-bold text-white transition-all"
                               style="background: linear-gradient(135deg, rgba(124,58,237,0.6), rgba(34,211,238,0.3)); border: 1px solid rgba(167,139,250,0.3);">
                                จัดการ →
                            </a>
                            @if (auth()->user()->canManageCourse($course))
                                <a href="{{ route('admin.courses.edit', $course) }}" class="btn-outline text-sm py-2.5 px-3">แก้ไข</a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 app-card p-12 text-center">
                    <p class="text-4xl mb-3">📚</p>
                    <p class="text-slate-500">ยังไม่มีคอร์สในระบบ</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
