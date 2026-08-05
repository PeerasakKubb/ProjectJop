<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">สร้างข้อสอบ — {{ $course->title }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <form method="POST" action="{{ route('admin.exams.store', $course) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium mb-1">ชื่อข้อสอบ</label>
                        <input type="text" name="title" required class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">รายละเอียด</label>
                        <textarea name="description" rows="3" class="w-full border rounded px-3 py-2"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">ประเภท</label>
                        <select name="type" class="w-full border rounded px-3 py-2">
                            <option value="multiple_choice">ปรนัย</option>
                            <option value="analytical">วิเคราะห์</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">เวลาทำ (นาที)</label>
                        <input type="number" name="time_limit_minutes" min="1" class="w-full border rounded px-3 py-2">
                    </div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_published" value="1">
                        <span class="text-sm">เปิดให้นักเรียนทำ</span>
                    </label>
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">สร้างข้อสอบ</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
