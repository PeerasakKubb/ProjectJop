@props(['module'])

@php
    $active = $module['active'] ?? false;
    $color = $module['layer_meta']['color'] ?? 'violet';
@endphp

<a
    href="{{ $module['url'] }}"
    @class([
        'sidebar-nav-item',
        "sidebar-nav-item--{$color}",
        'sidebar-nav-item--active' => $active,
    ])
>
    <span class="sidebar-nav-item__icon">{{ $module['icon'] }}</span>
    <span class="sidebar-nav-item__label">{{ $module['label'] }}</span>
    @if ($active)
        <span class="sidebar-nav-item__active-dot"></span>
    @endif
</a>
