@props([
    'device',
    'buttonClass' => 'px-6 py-2.5 rounded-xl font-semibold text-white transition-all',
])

<form method="POST" action="{{ route('admin.devices.toggle', $device) }}">
    @csrf
    <button
        type="submit"
        class="{{ $buttonClass }} {{ $device->is_on ? 'bg-rose-500 hover:bg-rose-600 shadow-md shadow-rose-500/25' : 'bg-emerald-500 hover:bg-emerald-600 shadow-md shadow-emerald-500/25' }}"
    >
        {{ $device->is_on ? 'ปิด' : 'เปิด' }}
    </button>
</form>
