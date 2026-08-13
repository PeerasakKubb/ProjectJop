<nav x-data="{ open: false }" class="glass-nav">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-14 sm:h-16">
            <div class="flex items-center gap-6 lg:gap-8">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                    <div class="w-9 h-9 flex items-center justify-center text-xs font-bold bg-brand-600 text-gold border border-gold">
                        SC
                    </div>
                    <span class="font-semibold text-white text-lg hidden sm:block">Smart Classroom</span>
                </a>

                <div class="hidden lg:flex items-center gap-0.5">
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">Dashboard</x-nav-link>
                    <x-nav-link :href="route('admin.attendance.index')" :active="request()->routeIs('admin.attendance.*')">เช็คชื่อ</x-nav-link>
                    <x-nav-link :href="route('admin.devices.index')" :active="request()->routeIs('admin.devices.*')">อุปกรณ์</x-nav-link>
                    <x-nav-link :href="route('admin.sensors.index')" :active="request()->routeIs('admin.sensors.*')">เซนเซอร์</x-nav-link>
                    <x-nav-link :href="route('admin.courses.index')" :active="request()->routeIs('admin.courses.*') || request()->routeIs('admin.lessons.*') || request()->routeIs('admin.exams.*')">คอร์ส</x-nav-link>
                    @if (auth()->user()->isAdmin() || auth()->user()->isTeacher())
                        <x-nav-link :href="route('admin.rfid.index')" :active="request()->routeIs('admin.rfid.*')">RFID</x-nav-link>
                        <x-nav-link :href="route('admin.notifications.index')" :active="request()->routeIs('admin.notifications.*')">แจ้งเตือน</x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center gap-2">
                @if (auth()->user()->isAdmin() || auth()->user()->isTeacher())
                    @php $unreadNotifications = \App\Models\SystemNotification::where('is_read', false)->count(); @endphp
                    <a href="{{ route('admin.notifications.index') }}" class="relative p-2 text-slate-400 hover:text-gold" title="การแจ้งเตือน">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @if ($unreadNotifications > 0)
                            <span class="absolute -top-0.5 -right-0.5 bg-rose-700 text-white text-[10px] font-bold min-w-[18px] h-[18px] flex items-center justify-center px-1">
                                {{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}
                            </span>
                        @endif
                    </a>
                @endif

                <span class="badge-brand capitalize hidden md:inline-flex text-xs">{{ auth()->user()->role }}</span>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2 pl-1 pr-2 py-1 hover:bg-white/5">
                            <div class="gov-initials w-8 h-8 flex items-center justify-center text-xs font-bold">
                                {{ mb_substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <span class="text-sm font-medium text-slate-300 max-w-[100px] truncate hidden md:block">{{ Auth::user()->name }}</span>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('admin.profile.edit')">โปรไฟล์</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">ออกจากระบบ</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="flex items-center sm:hidden">
                <button @click="open = ! open" class="p-2 rounded-xl text-slate-400 hover:bg-white/5">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-white/5 px-3 pb-4">
        <div class="pt-2 space-y-1">
            <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">Dashboard</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.attendance.index')" :active="request()->routeIs('admin.attendance.*')">เช็คชื่อ</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.devices.index')" :active="request()->routeIs('admin.devices.*')">อุปกรณ์</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.sensors.index')" :active="request()->routeIs('admin.sensors.*')">เซนเซอร์</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.courses.index')" :active="request()->routeIs('admin.courses.*')">คอร์ส</x-responsive-nav-link>
        </div>
        <div class="pt-3 mt-3 border-t border-white/5 px-1">
            <div class="font-semibold text-white text-sm">{{ Auth::user()->name }}</div>
            <div class="text-xs text-slate-500 mb-2">{{ Auth::user()->email }}</div>
            <x-responsive-nav-link :href="route('admin.profile.edit')">โปรไฟล์</x-responsive-nav-link>
        </div>
    </div>
</nav>
