<form id="send-verification" method="POST" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="POST" action="{{ route('profile.update') }}" class="row g-3">
    @csrf
    @method('PATCH')

    <div class="col-12">
        <label for="name" class="form-label">Nama</label>
        <input id="name" name="name" type="text" class="form-control form-control-sm" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
        @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label for="email" class="form-label">Email</label>
        <input id="email" name="email" type="email" class="form-control form-control-sm" value="{{ old('email', $user->email) }}" required autocomplete="username">
        @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="alert alert-warning mt-3 mb-0">
                Email Anda belum terverifikasi.
                <button form="send-verification" class="btn btn-link btn-sm p-0 align-baseline">Kirim ulang verifikasi</button>

                @if (session('status') === 'verification-link-sent')
                    <div class="small text-success mt-2">Link verifikasi baru sudah dikirim ke email Anda.</div>
                @endif
            </div>
        @endif
    </div>

    <div class="col-12 d-flex justify-content-end">
        <button class="btn btn-primary btn-sm">Simpan Profil</button>
    </div>
</form>
