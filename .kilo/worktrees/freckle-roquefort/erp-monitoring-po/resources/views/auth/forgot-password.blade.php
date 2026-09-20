<x-guest-layout>
    <p class="login-box-msg">Ajukan permintaan reset password dengan NIK. Administrator akan memproses permintaan Anda setelah diverifikasi.</p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="form-group mb-3">
            <label for="nik">NIK</label>
            <input id="nik" type="text" name="nik" value="{{ old('nik') }}" class="form-control form-control-sm @error('nik') is-invalid @enderror" required autofocus>
            @error('nik')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
        </div>

        <div class="form-group mb-3">
            <label for="request_note">Keterangan Permintaan</label>
            <textarea id="request_note" name="request_note" rows="4" class="form-control form-control-sm @error('request_note') is-invalid @enderror" required>{{ old('request_note') }}</textarea>
            @error('request_note')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
        </div>

        <button type="submit" class="btn btn-primary btn-sm btn-block">Ajukan Reset Password</button>
    </form>

    <p class="mb-0 mt-3">
        <a href="{{ route('login') }}">Kembali ke halaman masuk</a>
    </p>
</x-guest-layout>
