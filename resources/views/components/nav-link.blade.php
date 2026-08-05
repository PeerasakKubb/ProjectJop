@props(['active'])

@php
$classes = ($active ?? false)
    ? 'inline-flex items-center px-3.5 py-2 rounded-xl text-sm font-bold text-white transition-all duration-300 shadow-glow'
    : 'inline-flex items-center px-3.5 py-2 rounded-xl text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 transition-all duration-200';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} @if($active ?? false) style="background: linear-gradient(135deg, rgba(124,58,237,0.8), rgba(99,102,241,0.6)); border: 1px solid rgba(167,139,250,0.4);" @endif>
    {{ $slot }}
</a>
