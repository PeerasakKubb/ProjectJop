<x-app-layout>
    <x-slot name="header">
        <x-module-header title="แก้ไขผู้ใช้" :description="$user->email" />
    </x-slot>

    <div class="page-content max-w-lg">
        <div class="app-card p-6">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="name" value="ชื่อ" />
                    <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $user->name)" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="email" value="อีเมล" />
                    <x-text-input id="email" type="email" name="email" class="block mt-1 w-full" :value="old('email', $user->email)" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="role" value="บทบาท" />
                    <select id="role" name="role" class="input-modern mt-1 w-full px-4 py-2.5">
                        @foreach (['admin' => 'Admin', 'teacher' => 'ครู', 'student' => 'นักเรียน'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('role', $user->role) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="password" value="รหัสผ่านใหม่ (เว้นว่างถ้าไม่เปลี่ยน)" />
                    <x-text-input id="password" type="password" name="password" class="block mt-1 w-full" />
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" value="ยืนยันรหัสผ่าน" />
                    <x-text-input id="password_confirmation" type="password" name="password_confirmation" class="block mt-1 w-full" />
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-brand">บันทึก</button>
                    <a href="{{ route('admin.users.index') }}" class="btn-outline">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
