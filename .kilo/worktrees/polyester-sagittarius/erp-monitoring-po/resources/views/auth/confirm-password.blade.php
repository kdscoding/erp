<x-guest-layout>
    <p class="login-box-msg">Area ini memerlukan konfirmasi password sebelum dilanjutkan.</p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="form-group mb-3">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" class="form-control form-control-sm @error('password') is-invalid @enderror" required autocomplete="current-password">
            @error('password')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
        </div>

        <button type="submit" class="btn btn-primary btn-sm btn-block">Konfirmasi</button>
    </form>
</x-guest-layout>
