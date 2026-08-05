@props(['label', 'value', 'icon' => null, 'color' => 'brand'])

@php
$variants = [
    'brand' => 'stat-card-neo--brand',
    'green' => 'stat-card-neo--green',
    'amber' => 'stat-card-neo--amber',
    'blue'  => 'stat-card-neo--blue',
    'rose'  => 'stat-card-neo--brand',
];
$glows = [
    'brand' => 'shadow-glow',
    'green' => 'shadow-[0_0_30px_rgba(16,185,129,0.25)]',
    'amber' => 'shadow-[0_0_30px_rgba(245,158,11,0.25)]',
    'blue'  => 'shadow-glow-cyan',
];
$v = $variants[$color] ?? $variants['brand'];
$g = $glows[$color] ?? $glows['brand'];
@endphp

<div {{ $attributes->merge(['class' => "stat-card-neo {$v} animate-slide-up"]) }}>
    <div class="relative z-10 flex items-start justify-between">
        <div>
            <p class="text-sm font-medium text-slate-400">{{ $label }}</p>
            <p class="text-4xl font-extrabold mt-1 text-white tracking-tight">{{ $value }}</p>
        </div>
        @if ($icon)
            <div class="text-3xl drop-shadow-lg animate-float">{{ $icon }}</div>
        @endif
    </div>
    @if (isset($slot) && ! $slot->isEmpty())
        <div class="relative z-10 mt-3 pt-3 border-t border-white/10">{{ $slot }}</div>
    @endif
</div>
