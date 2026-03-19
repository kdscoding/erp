<x-guest-layout>
    <div class="p-4 border rounded" style="border-color:#bcd0ec!important;background:#f3f8ff;">
        <div class="text-center mb-4">
            <div class="fw-bold" style="font-size:20px;color:#004b9b;">PORTAL BC 4.0 INTERNAL</div>
            <div class="text-muted" style="font-size:12px;">Tampilan terinspirasi CEISA Beacukai untuk monitoring operasional perusahaan</div>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="block mt-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                    <span class="ms-2 text-sm text-gray-600">Ingat saya</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                        Lupa password?
                    </a>
                @endif

                <x-primary-button class="ms-3">
                    Masuk
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
