<x-app-layout>
    <x-slot name="header">
        <x-module-header title="จัดการผู้ใช้" description="Admin · ครู · นักเรียน">
            <x-slot:actions>
                <a href="{{ route('admin.users.create') }}" class="btn-brand">+ เพิ่มผู้ใช้</a>
            </x-slot:actions>
        </x-module-header>
    </x-slot>

    <div class="page-content">
        @if (session('success'))
            <div class="flash-success mb-6">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="flash-error mb-6">{{ session('error') }}</div>
        @endif

        <div class="app-card overflow-hidden">
            <table class="w-full text-sm">
                <thead class="border-b border-white/10 text-slate-400 text-left">
                    <tr>
                        <th class="px-5 py-3 font-semibold">ชื่อ</th>
                        <th class="px-5 py-3 font-semibold">อีเมล</th>
                        <th class="px-5 py-3 font-semibold">บทบาท</th>
                        <th class="px-5 py-3 font-semibold text-right">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                            <td class="px-5 py-3 text-white font-medium">{{ $user->name }}</td>
                            <td class="px-5 py-3 text-slate-400">{{ $user->email }}</td>
                            <td class="px-5 py-3">
                                <span class="badge-brand capitalize">{{ $user->role }}</span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn-ghost text-xs">แก้ไข</a>
                                @if ($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('ลบผู้ใช้นี้?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-400 text-xs ml-2 hover:underline">ลบ</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $users->links() }}</div>
    </div>
</x-app-layout>
