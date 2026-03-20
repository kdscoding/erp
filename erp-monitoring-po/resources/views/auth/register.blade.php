<x-guest-layout>
    <p class="login-box-msg">Pendaftaran publik tidak aktif. Halaman ini disediakan untuk kebutuhan internal sistem.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="form-group mb-3">
            <label for="name">Nama</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required autofocus autocomplete="name">
            @error('name')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
        </div>

        <div class="form-group mb-3">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autocomplete="username">
            @error('email')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
        </div>

        <div class="form-group mb-3">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
            @error('password')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
        </div>

        <div class="form-group mb-3">
            <label for="password_confirmation">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" required autocomplete="new-password">
            @error('password_confirmation')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('login') }}">Sudah punya akun?</a>
            <button type="submit" class="btn btn-primary">Daftar</button>
        </div>
    </form>
</x-guest-layout>
