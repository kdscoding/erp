<x-guest-layout>
    <p class="login-box-msg">Verifikasi alamat email diperlukan sebelum Anda melanjutkan.</p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success">
            Link verifikasi baru sudah dikirim ke email Anda.
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
        @csrf
        <button type="submit" class="btn btn-primary btn-block">Kirim Ulang Verifikasi Email</button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline-secondary btn-block">Keluar</button>
    </form>
</x-guest-layout>
