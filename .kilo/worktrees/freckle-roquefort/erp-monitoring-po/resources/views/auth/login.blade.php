<x-guest-layout>
    <p class="login-box-msg">Masuk menggunakan NIK untuk mengakses modul purchase order, shipment, dan receiving.</p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="form-group mb-3">
            <label for="nik" class="small text-muted text-uppercase mb-1">NIK</label>
            <div class="input-group">
            <input id="nik" type="text" name="nik" value="{{ old('nik') }}" class="form-control form-control-sm @error('nik') is-invalid @enderror" placeholder="NIK" required autofocus autocomplete="username">
            <div class="input-group-append"><div class="input-group-text"><span class="fas fa-id-card"></span></div></div>
            </div>
            @error('nik')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
        </div>

        <div class="form-group mb-3">
            <label for="password" class="small text-muted text-uppercase mb-1">Password</label>
            <div class="input-group">
            <input id="password" type="password" name="password" class="form-control form-control-sm @error('password') is-invalid @enderror" placeholder="Password" required autocomplete="current-password">
            <div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>
            </div>
            @error('password')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
        </div>

        <div class="row align-items-center">
            <div class="col-sm-6">
                <div class="icheck-primary">
                    <input id="remember_me" type="checkbox" name="remember">
                    <label for="remember_me">Ingat saya</label>
                </div>
            </div>
            <div class="col-sm-6 mt-2 mt-sm-0">
                <button type="submit" class="btn btn-primary btn-sm btn-block">Masuk ke Sistem</button>
            </div>
        </div>
    </form>

    @if (Route::has('password.request'))
        <p class="mb-0 mt-3 text-center">
            <a href="{{ route('password.request') }}">Lupa password?</a>
        </p>
    @endif
</x-guest-layout>
