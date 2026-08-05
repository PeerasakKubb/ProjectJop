<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <h2 class="text-2xl font-black text-white mb-1">เข้าสู่ระบบ</h2>
    <p class="text-sm text-slate-400 mb-6">เข้าสู่ระบบเพื่อใช้งานหลังบ้าน</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" value="อีเมล" />
            <x-text-input id="email" class="block mt-1.5 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="รหัสผ่าน" />
            <x-text-input id="password" class="block mt-1.5 w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500" name="remember">
                <span class="ms-2 text-sm text-slate-400">จดจำฉัน</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-6">
            @if (Route::has('password.request'))
                <a class="text-sm text-cyan-400 hover:text-cyan-300" href="{{ route('password.request') }}">ลืมรหัสผ่าน?</a>
            @endif
            <x-primary-button>เข้าสู่ระบบ</x-primary-button>
        </div>
    </form>

    <p class="text-center text-sm text-slate-500 mt-6">
        ยังไม่มีบัญชี? <a href="{{ route('register') }}" class="text-violet-400 font-semibold hover:text-cyan-400">สมัครสมาชิก</a>
    </p>
</x-guest-layout>
