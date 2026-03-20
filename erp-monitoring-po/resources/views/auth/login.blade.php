<x-guest-layout>
    <p class="login-box-msg mb-4">Masuk menggunakan NIK untuk mengakses portal operasional internal.</p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="input-group mb-3">
            <input id="nik" type="text" name="nik" value="{{ old('nik') }}" class="form-control form-control-sm @error('nik') is-invalid @enderror" placeholder="NIK" required autofocus autocomplete="username">
            <div class="input-group-append"><div class="input-group-text"><span class="fas fa-id-card"></span></div></div>
            @error('nik')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
        </div>

        <div class="input-group mb-3">
            <input id="password" type="password" name="password" class="form-control form-control-sm @error('password') is-invalid @enderror" placeholder="Password" required autocomplete="current-password">
            <div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>
            @error('password')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
        </div>

        <div class="row">
            <div class="col-6">
                <div class="icheck-primary">
                    <input id="remember_me" type="checkbox" name="remember">
                    <label for="remember_me">Ingat saya</label>
                </div>
            </div>
            <div class="col-6">
                <button type="submit" class="btn btn-primary btn-sm btn-block">Masuk</button>
            </div>
        </div>
    </form>

    @if (Route::has('password.request'))
        <p class="mb-1 mt-3">
            <a href="{{ route('password.request') }}">Lupa password?</a>
        </p>
    @endif
</x-guest-layout>
