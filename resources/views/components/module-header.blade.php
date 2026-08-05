@props(['module' => null, 'title' => null, 'description' => null])

@php
    $meta = is_string($module)
        ? \App\Support\SmartClassroom::module($module)
        : ($module ?? \App\Support\SmartClassroom::currentModule());

    $layerColor = $meta['layer_meta']['color'] ?? 'violet';
    $layerLabel = $meta['layer_meta']['label'] ?? '';
    $heading = $title ?? ($meta['label'] ?? '');
    $desc = $description ?? ($meta['description'] ?? '');
@endphp

<div {{ $attributes->merge(['class' => 'module-header']) }}>
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            @if ($meta)
                <div class="module-header__badge module-header__badge--{{ $layerColor }}">
                    <span class="module-header__layer-dot module-header__layer-dot--{{ $layerColor }}"></span>
                    {{ $layerLabel }}
                </div>
            @endif
            <h1 class="module-header__title">{{ $heading }}</h1>
            @if ($desc)
                <p class="module-header__desc">{{ $desc }}</p>
            @endif
        </div>

        @if (isset($actions))
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
