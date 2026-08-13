@php
    $layers = $smartClassroomLayers ?? [];
    $grouped = $smartClassroomModules ?? [];
    $current = \App\Support\SmartClassroom::currentModule();
@endphp

<aside
    x-data="{ open: false }"
    class="app-sidebar"
    :class="{ 'app-sidebar--open': open }"
>
    <button
        type="button"
        @click="open = !open"
        class="app-sidebar__toggle lg:hidden"
        aria-label="เปิดเมนู"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    <div class="app-sidebar__panel" @click.outside="open = false">
        <div class="app-sidebar__brand">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 flex items-center justify-center text-gold text-sm font-bold border border-gold">
                    SC
                </div>
                <div class="min-w-0">
                    <p class="font-semibold text-white text-sm leading-tight">Smart Classroom</p>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-gold">หลังบ้าน · Admin</p>
                </div>
            </a>
            <a href="{{ route('home') }}" class="mt-3 flex items-center gap-2 px-2 py-1.5 text-xs text-slate-500 hover:text-gold">
                ← กลับหน้าบ้าน
            </a>
        </div>

        <nav class="app-sidebar__nav">
            @foreach ($grouped as $layerKey => $group)
                @php $layer = $group['meta']; @endphp
                <div class="app-sidebar__group" data-layer="{{ $layerKey }}">
                    <div class="app-sidebar__group-label">
                        <span class="app-sidebar__layer-dot"></span>
                        <span>{{ $layer['label'] }}</span>
                    </div>
                    <p class="app-sidebar__group-sub">{{ $layer['subtitle'] }}</p>

                    <ul class="space-y-0.5">
                        @foreach ($group['modules'] as $module)
                            <li>
                                <x-sidebar-nav-item :module="$module" />
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>

        <div class="app-sidebar__footer">
            @if (auth()->user()->isAdmin() || auth()->user()->isTeacher())
                @php
                    $unreadNotifications = class_exists(\App\Models\SystemNotification::class)
                        ? \App\Models\SystemNotification::where('is_read', false)->count()
                        : 0;
                @endphp
                <a href="{{ route('admin.notifications.index') }}" class="app-sidebar__alert" title="การแจ้งเตือน">
                    <span>แจ้งเตือน</span>
                    @if ($unreadNotifications > 0)
                        <span class="badge-danger">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>
                    @endif
                </a>
            @endif

            <a href="{{ route('admin.profile.edit') }}" class="app-sidebar__user">
                <div class="gov-initials w-8 h-8 flex items-center justify-center text-xs font-bold">
                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-slate-500 capitalize">{{ auth()->user()->role }}</p>
                </div>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="app-sidebar__logout">ออกจากระบบ</button>
            </form>
        </div>
    </div>

    <div
        x-show="open"
        x-transition.opacity
        class="app-sidebar__overlay lg:hidden"
        @click="open = false"
    ></div>
</aside>
