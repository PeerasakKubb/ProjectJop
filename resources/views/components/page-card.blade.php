@props(['title' => null, 'action' => null, 'actionLabel' => 'ดูทั้งหมด'])

<div {{ $attributes->merge(['class' => 'app-card p-6']) }}>
    @if ($title || isset($heading))
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-bold text-white">
                @isset($heading)
                    {{ $heading }}
                @else
                    {{ $title }}
                @endisset
            </h3>
            @if ($action)
                <a href="{{ $action }}" class="btn-ghost">{{ $actionLabel }} →</a>
            @endif
        </div>
    @endif
    {{ $slot }}
</div>
