@props([
    'device',
    'buttonClass' => 'px-6 py-2.5 rounded-xl font-semibold text-white transition-all',
])

<form
    x-data="deviceToggle(@json((bool) $device->is_on))"
    @submit.prevent="submit('{{ route('admin.devices.toggle', $device) }}')"
>
    @csrf
    <button
        type="submit"
        class="{{ $buttonClass }}"
        :class="isOn
            ? 'bg-rose-500 hover:bg-rose-600 shadow-md shadow-rose-500/25'
            : 'bg-emerald-500 hover:bg-emerald-600 shadow-md shadow-emerald-500/25'"
        :disabled="loading"
    >
        <span x-show="!loading" x-text="isOn ? 'ปิด' : 'เปิด'"></span>
        <span x-cloak x-show="loading">...</span>
    </button>
</form>
