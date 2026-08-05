<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">จัดการบัตร RFID</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="p-4 bg-green-100 text-green-700 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold mb-4">ลงทะเบียนบัตรใหม่</h3>
                <form method="POST" action="{{ route('admin.rfid.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @csrf
                    <select name="user_id" required class="border rounded px-3 py-2">
                        <option value="">เลือกนักเรียน</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}">{{ $student->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="card_uid" placeholder="Card UID (เช่น A1B2C3D4)" required class="border rounded px-3 py-2 font-mono">
                    <button type="submit" class="bg-indigo-600 text-white rounded px-4 py-2 hover:bg-indigo-700">ลงทะเบียน</button>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">นักเรียน</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Card UID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">สถานะ</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($cards as $card)
                            <tr>
                                <td class="px-6 py-4 font-medium">{{ $card->user->name }}</td>
                                <td class="px-6 py-4 font-mono text-sm">{{ $card->card_uid }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded text-xs {{ $card->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $card->is_active ? 'ใช้งาน' : 'ปิดใช้งาน' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('admin.rfid.destroy', $card) }}" method="POST" onsubmit="return confirm('ลบบัตรนี้?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 text-sm hover:underline">ลบ</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">ยังไม่มีบัตร RFID ลงทะเบียน</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">{{ $cards->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
