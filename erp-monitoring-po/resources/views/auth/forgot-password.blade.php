<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Ajukan permintaan reset password dengan NIK. Administrator akan memproses reset setelah permintaan Anda diterima.
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
            <x-input-label for="nik" :value="__('NIK')" />
            <x-text-input id="nik" class="block mt-1 w-full" type="text" name="nik" :value="old('nik')" required autofocus />
            <x-input-error :messages="$errors->get('nik')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="request_note" :value="__('Keterangan Permintaan')" />
            <textarea id="request_note" name="request_note" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="4" required>{{ old('request_note') }}</textarea>
            <x-input-error :messages="$errors->get('request_note')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Ajukan Reset Password
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
