@php
    $formAction = isset($course)
        ? route('admin.courses.update', $course)
        : route('admin.courses.store');
    $isEdit = isset($course);
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-module-header :title="$isEdit ? 'แก้ไขคอร์ส' : 'สร้างคอร์สใหม่'" module="courses" />
    </x-slot>

    <div class="page-content max-w-2xl">
        <div class="app-card p-6">
            @if ($errors->any())
                <div class="flash-error mb-6">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ $formAction }}" class="space-y-4">
                @csrf
                @if ($isEdit) @method('PUT') @endif

                <div>
                    <x-input-label for="title" value="ชื่อคอร์ส" />
                    <x-text-input id="title" name="title" class="block mt-1 w-full" :value="old('title', $course->title ?? '')" required />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="subject" value="วิชา" />
                        <x-text-input id="subject" name="subject" class="block mt-1 w-full" :value="old('subject', $course->subject ?? '')" />
                    </div>
                    <div>
                        <x-input-label for="grade_level" value="ระดับชั้น" />
                        <x-text-input id="grade_level" name="grade_level" class="block mt-1 w-full" :value="old('grade_level', $course->grade_level ?? '')" placeholder="เช่น ม.4" />
                    </div>
                </div>

                <div>
                    <x-input-label for="description" value="คำอธิบาย" />
                    <textarea id="description" name="description" rows="4" class="input-modern mt-1 w-full px-4 py-2.5">{{ old('description', $course->description ?? '') }}</textarea>
                </div>

                <div>
                    <x-input-label for="price" value="ราคา (บาท) — 0 = ฟรี" />
                    <x-text-input id="price" type="number" name="price" step="0.01" min="0" class="block mt-1 w-full" :value="old('price', $course->price ?? 0)" required />
                </div>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_published" value="1" class="rounded border-slate-600 text-violet-500"
                           @checked(old('is_published', $course->is_published ?? true))>
                    <span class="text-sm text-slate-300">เผยแพร่บนหน้าบ้าน (แสดงใน /courses)</span>
                </label>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-brand">{{ $isEdit ? 'บันทึก' : 'สร้างคอร์ส' }}</button>
                    <a href="{{ route('admin.courses.index') }}" class="btn-outline">ยกเลิก</a>
                </div>
            </form>

            @if ($isEdit)
                <form action="{{ route('admin.courses.destroy', $course) }}" method="POST" class="mt-4 pt-4 border-t border-white/10" onsubmit="return confirm('ลบคอร์สนี้ถาวร?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-rose-400 text-sm hover:underline">ลบคอร์สถาวร</button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
