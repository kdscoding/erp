<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Portal BC 4.0 Internal' }}</title>
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('lemon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('lemon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('lemon/favicon-16x16.png') }}">
    <link rel="shortcut icon" href="{{ asset('lemon/favicon.ico') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        body.login-page {
            background:
                radial-gradient(circle at top left, rgba(255, 214, 90, 0.18), transparent 32%),
                radial-gradient(circle at top right, rgba(119, 221, 119, 0.16), transparent 28%),
                linear-gradient(135deg, #f5f2f8 0%, #ece5f4 100%);
        }
        .login-box {
            width: 440px;
        }
        .login-logo a {
            color: #3a2e58;
            font-weight: 700;
        }
        .login-logo .login-main-mark {
            width: 76px;
            height: 76px;
            object-fit: contain;
            filter: drop-shadow(0 0.35rem 1rem rgba(58, 46, 88, 0.18));
        }
        .login-logo .login-brand-mark {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            box-shadow: 0 0.125rem 0.5rem rgba(58, 46, 88, 0.16);
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
            color: #6c757d;
        }
        .login-logo .login-context {
            color: #4c3d68;
            font-size: .95rem;
        }
        .login-card {
            border: 0;
            box-shadow: 0 0.9rem 2rem rgba(55, 41, 90, 0.12);
            border-radius: .9rem;
            overflow: hidden;
        }
        .login-card-body {
            border-radius: .9rem;
            padding: 1.75rem;
        }
        .login-box-msg {
            color: #5d5472;
            font-size: .93rem;
            margin-bottom: 1.25rem;
        }
        .login-helper {
            color: #7a708f;
            font-size: .8rem;
            text-align: center;
            margin-top: 1rem;
        }
    </style>
</head>
<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-logo">
            <a href="{{ url('/') }}" class="d-flex flex-column align-items-center">
                <img src="{{ asset('lemon/android-chrome-512x512.png') }}" alt="LEMON" class="login-main-mark mb-3">
                <div class="text-center mb-2">
                    <div class="login-title">LEMON</div>
                    <div class="login-subtitle">Labeling Internal Monitoring</div>
                </div>
                <div class="d-flex align-items-center">
                    <img src="{{ asset('lemon/apple-touch-icon.png') }}" alt="LEMON icon" class="login-brand-mark mr-2">
                    <span class="login-context">Portal <b>Operasional Internal</b></span>
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
