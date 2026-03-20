<div class="mb-3 text-muted">
    Akun tidak akan dihapus permanen. Sistem akan menonaktifkan akun ini agar tidak bisa digunakan untuk login kembali.
</div>

<form method="POST" action="{{ route('profile.destroy') }}" class="row g-3">
    @csrf
    @method('DELETE')

    <div class="col-md-6">
        <label for="password" class="form-label">Konfirmasi Password</label>
        <input id="password" name="password" type="password" class="form-control" placeholder="Masukkan password untuk konfirmasi">
        @if($errors->userDeletion->has('password'))<div class="text-danger small mt-1">{{ $errors->userDeletion->first('password') }}</div>@endif
    </div>

    <div class="col-12 d-flex justify-content-end">
        <button class="btn btn-danger">Nonaktifkan Akun</button>
    </div>
</form>
