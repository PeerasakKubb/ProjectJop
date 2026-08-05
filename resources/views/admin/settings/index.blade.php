<x-app-layout>
    <x-slot name="header">
        <x-module-header title="ตั้งค่าเว็บไซต์" description="แก้ไขเนื้อหาหน้าบ้าน — Hero, สถิติ, ข้อมูลติดต่อ">
            <x-slot:actions>
                <a href="{{ route('home') }}" target="_blank" class="btn-outline text-sm">ดูหน้าบ้าน ↗</a>
            </x-slot:actions>
        </x-module-header>
    </x-slot>

    <div class="page-content max-w-3xl">
        @if (session('success'))
            <div class="flash-success mb-6">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-8">
            @csrf
            @method('PUT')

            @foreach ($settings as $group => $items)
                <div class="app-card p-6">
                    <h3 class="text-lg font-bold text-white mb-4 capitalize">{{ $group }}</h3>
                    <div class="space-y-4">
                        @foreach ($items as $setting)
                            <div>
                                <label for="setting_{{ $setting->key }}" class="block text-sm font-medium text-slate-300 mb-1.5">
                                    {{ $setting->label }}
                                </label>
                                @if ($setting->type === 'textarea')
                                    <textarea
                                        id="setting_{{ $setting->key }}"
                                        name="settings[{{ $setting->key }}]"
                                        rows="3"
                                        class="input-modern px-4 py-2.5"
                                    >{{ old("settings.{$setting->key}", $setting->value) }}</textarea>
                                @else
                                    <input
                                        type="text"
                                        id="setting_{{ $setting->key }}"
                                        name="settings[{{ $setting->key }}]"
                                        value="{{ old("settings.{$setting->key}", $setting->value) }}"
                                        class="input-modern px-4 py-2.5"
                                    >
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="flex justify-end">
                <button type="submit" class="btn-brand">บันทึกการตั้งค่า</button>
            </div>
        </form>
    </div>
</x-app-layout>
