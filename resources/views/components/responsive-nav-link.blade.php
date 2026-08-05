@props(['active'])

@php
$classes = ($active ?? false)
    ? 'block w-full ps-3 pe-4 py-2.5 rounded-xl text-base font-bold text-white'
    : 'block w-full ps-3 pe-4 py-2.5 rounded-xl text-base font-medium text-slate-400 hover:text-white hover:bg-white/5 transition-colors';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} @if($active ?? false) style="background: linear-gradient(135deg, rgba(124,58,237,0.5), rgba(99,102,241,0.3));" @endif>
    {{ $slot }}
</a>
