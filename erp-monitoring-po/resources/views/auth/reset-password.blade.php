<x-guest-layout>
    <p class="login-box-msg">Tetapkan password baru untuk akun Anda.</p>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="form-group mb-3">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" class="form-control @error('email') is-invalid @enderror" required autofocus autocomplete="username">
            @error('email')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
        </div>

        <div class="form-group mb-3">
            <label for="password">Password Baru</label>
            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
            @error('password')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
        </div>

        <div class="form-group mb-3">
            <label for="password_confirmation">Konfirmasi Password Baru</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" required autocomplete="new-password">
            @error('password_confirmation')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
        </div>

        <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
    </form>
</x-guest-layout>
