@props([
    'compact' => false,
    'showFlow' => true,
    'modules' => null,
    'layers' => null,
])

@php
    use App\Support\SmartClassroom;

    $layers = $layers ?? SmartClassroom::layers();
    $grouped = [];

    if ($modules) {
        foreach ($layers as $layerKey => $layer) {
            $grouped[$layerKey] = ['meta' => $layer, 'modules' => []];
        }
        foreach ($modules as $key => $module) {
            $grouped[$module['layer']]['modules'][$key] = $module;
        }
        $grouped = array_filter($grouped, fn ($g) => count($g['modules']) > 0);
    } else {
        $grouped = SmartClassroom::modulesByLayer(auth()->user());
    }

    $layerOrder = ['input', 'control', 'learning', 'hub', 'admin'];
    $ordered = collect($layerOrder)
        ->filter(fn ($k) => isset($grouped[$k]))
        ->mapWithKeys(fn ($k) => [$k => $grouped[$k]])
        ->all();
@endphp

<div {{ $attributes->merge(['class' => 'system-diagram' . ($compact ? ' system-diagram--compact' : '')]) }}>
    @if (! $compact)
        <div class="system-diagram__header">
            <p class="text-xs font-bold uppercase tracking-widest text-cyan-400">Architecture</p>
            <h3 class="text-lg font-bold text-white">แผนภาพโมดูลระบบ</h3>
            <p class="text-sm text-slate-500 mt-1">ข้อมูลเข้า → ควบคุม → เรียนรู้ → ศูนย์กลาง Dashboard</p>
        </div>
    @endif

    <div class="system-diagram__canvas">
        @foreach ($ordered as $layerKey => $group)
            @php $layer = $group['meta']; @endphp
            <div class="system-diagram__layer system-diagram__layer--{{ $layer['color'] ?? 'violet' }}" data-layer="{{ $layerKey }}">
                <div class="system-diagram__layer-head">
                    <span class="system-diagram__layer-dot system-diagram__layer-dot--{{ $layer['color'] ?? 'violet' }}"></span>
                    <span class="system-diagram__layer-title">{{ $layer['label'] }}</span>
                </div>
                <p class="system-diagram__layer-sub">{{ $layer['subtitle'] }}</p>

                <div class="system-diagram__nodes">
                    @foreach ($group['modules'] as $key => $module)
                        @if ($key === 'architecture' && $compact)
                            @continue
                        @endif
                        <a
                            href="{{ $module['url'] ?? (Route::has($module['route']) ? route($module['route']) : '#') }}"
                            @class([
                                'system-diagram__node',
                                'system-diagram__node--active' => $module['active'] ?? SmartClassroom::isModuleActive($module),
                            ])
                        >
                            <span class="system-diagram__node-icon">{{ $module['icon'] }}</span>
                            <span class="system-diagram__node-label">{{ $module['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            @if (! $loop->last && $showFlow)
                <div class="system-diagram__arrow" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" class="w-5 h-5 text-slate-600">
                        <path d="M12 5v14M5 12l7 7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            @endif
        @endforeach
    </div>

    @if (! $compact)
        <div class="system-diagram__hub">
            <div class="system-diagram__hub-ring"></div>
            <div class="system-diagram__hub-core">
                <span class="text-2xl">📊</span>
                <span class="text-sm font-bold text-white">Dashboard Hub</span>
                <span class="text-[10px] text-slate-500">รวบรวมข้อมูลทุกชั้น</span>
            </div>
        </div>
    @endif
</div>
