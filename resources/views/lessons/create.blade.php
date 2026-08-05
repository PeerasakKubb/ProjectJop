<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">เพิ่มบทเรียน — {{ $course->title }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <form method="POST" action="{{ route('admin.lessons.store', $course) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อบทเรียน</label>
                        <input type="text" name="title" required class="w-full border rounded px-3 py-2" value="{{ old('title') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">เนื้อหา</label>
                        <textarea name="content" rows="8" class="w-full border rounded px-3 py-2">{{ old('content') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ลิงก์วิดีโอ (ถ้ามี)</label>
                        <input type="url" name="video_url" class="w-full border rounded px-3 py-2" value="{{ old('video_url') }}">
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">บันทึก</button>
                        <a href="{{ route('admin.courses.show', $course) }}" class="px-6 py-2 border rounded text-gray-600 hover:bg-gray-50">ยกเลิก</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
