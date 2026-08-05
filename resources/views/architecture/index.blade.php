<x-app-layout>
    <x-slot name="header">
        <x-module-header module="architecture" />
    </x-slot>

    <div class="page-content">
        <div class="max-w-7xl mx-auto space-y-8">
            <x-system-diagram :modules="$modules" :layers="$layers" class="app-card p-6 lg:p-8" />

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach ($layers as $layerKey => $layer)
                    @php
                        $layerModules = collect($modules)->filter(fn ($m) => $m['layer'] === $layerKey);
                    @endphp
                    @if ($layerModules->isNotEmpty())
                        <div class="app-card p-6">
                            <div class="flex items-center gap-2 mb-4">
                                <span class="module-header__layer-dot module-header__layer-dot--{{ $layer['color'] }}"></span>
                                <h3 class="font-bold text-white">{{ $layer['label'] }}</h3>
                            </div>
                            <p class="text-sm text-slate-500 mb-4">{{ $layer['subtitle'] }}</p>
                            <ul class="space-y-3">
                                @foreach ($layerModules as $mod)
                                    <li class="flex gap-3 p-3 rounded-xl bg-white/5 border border-white/8">
                                        <span class="text-xl">{{ $mod['icon'] }}</span>
                                        <div>
                                            <p class="font-semibold text-white text-sm">{{ $mod['label'] }}</p>
                                            <p class="text-xs text-slate-500 mt-0.5">{{ $mod['description'] }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endforeach
            </div>

            <x-page-card title="การไหลของข้อมูล (Data Flow)">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach ($flow as $edge)
                        @php
                            $from = \App\Support\SmartClassroom::module($edge['from']);
                            $to = \App\Support\SmartClassroom::module($edge['to']);
                        @endphp
                        @if ($from && $to)
                            <div class="flex items-center gap-2 p-3 rounded-xl bg-white/5 text-sm">
                                <span>{{ $from['icon'] }}</span>
                                <span class="text-white font-medium">{{ $from['label'] }}</span>
                                <span class="text-slate-600">→</span>
                                <span>{{ $to['icon'] }}</span>
                                <span class="text-white font-medium">{{ $to['label'] }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </x-page-card>
        </div>
    </div>
</x-app-layout>
