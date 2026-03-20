<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'LEMON | Login' }}</title>
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('lemon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('lemon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('lemon/favicon-16x16.png') }}">
    <link rel="shortcut icon" href="{{ asset('lemon/favicon.ico') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        body.login-page {
            background:
                radial-gradient(circle at 15% 20%, rgba(255, 223, 72, 0.32), transparent 20%),
                radial-gradient(circle at 82% 16%, rgba(142, 214, 70, 0.22), transparent 18%),
                radial-gradient(circle at 70% 78%, rgba(255, 241, 176, 0.26), transparent 22%),
                linear-gradient(135deg, #fffbea 0%, #f6f7df 38%, #eef4df 100%);
        }
        .login-box {
            width: 440px;
        }
        .login-logo a {
            color: #304218;
            font-weight: 700;
        }
        .login-logo .login-main-mark {
            width: 76px;
            height: 76px;
            object-fit: contain;
            filter: drop-shadow(0 0.35rem 1rem rgba(58, 46, 88, 0.18));
        }
        .login-logo .login-title {
            font-size: 1.75rem;
            letter-spacing: .28rem;
            line-height: 1;
            font-weight: 700;
        }
        .login-logo .login-subtitle {
            font-size: .88rem;
            letter-spacing: .05rem;
            color: #6a7a42;
        }
        .login-card {
            border: 0;
            box-shadow: 0 1rem 2.4rem rgba(106, 122, 66, 0.14);
            border-radius: .9rem;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.95);
        }
        .login-card-body {
            border-radius: .9rem;
            padding: 1.75rem;
            border-top: 4px solid #d8e84f;
        }
        .login-box-msg {
            color: #61713e;
            font-size: .93rem;
            margin-bottom: 1.25rem;
        }
        .login-helper {
            color: #7f8b55;
            font-size: .8rem;
            text-align: center;
            margin-top: 1rem;
        }
        .login-card .form-control {
            border-color: #dfe8b3;
            background: #fffef7;
        }
        .login-card .form-control:focus {
            border-color: #b6cf45;
            box-shadow: 0 0 0 0.12rem rgba(182, 207, 69, 0.2);
            background: #fff;
        }
        .login-card .input-group-text {
            background: #f7f8dd;
            border-color: #dfe8b3;
            color: #73822b;
        }
        .login-card .btn-primary {
            background: linear-gradient(135deg, #bfd730 0%, #8fc63f 100%);
            border-color: #8eb93a;
            color: #24310f;
            font-weight: 700;
        }
        .login-card .btn-primary:hover,
        .login-card .btn-primary:focus {
            background: linear-gradient(135deg, #b0ca22 0%, #82ba34 100%);
            border-color: #7aa52f;
            color: #1f290d;
        }
        .login-card a {
            color: #6d8d1f;
            font-weight: 600;
        }
        .login-card a:hover {
            color: #587312;
        }
        .login-card .icheck-primary>input:first-child:checked+label::before,
        .login-card .icheck-primary>input:first-child:not(:checked):not(:disabled):hover+label::before {
            background-color: #9cc43b;
            border-color: #88ab2f;
        }
    </style>
</head>
<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-logo">
            <a href="{{ url('/') }}" class="d-flex flex-column align-items-center">
                <img src="{{ asset('lemon/android-chrome-512x512.png') }}" alt="LEMON" class="login-main-mark mb-3">
                <div class="text-center mb-3">
                    <div class="login-title">LEMON</div>
                    <div class="login-subtitle">Labeling Internal Monitoring</div>
                </div>
            </a>
        </div>
        <div class="card login-card">
            <div class="card-body login-card-body">
                {{ $slot }}
                <div class="login-helper">Akses dibatasi untuk pengguna internal yang telah terdaftar.</div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
