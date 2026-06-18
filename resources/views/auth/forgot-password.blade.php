<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Aplikasi Berbagi Data</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px;
            background: #0a1628;
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
        }
        .auth-card {
            width: 100%;
            max-width: 460px;
            background: #fff;
            border-radius: 12px;
            padding: 34px;
            box-shadow: 0 22px 70px rgba(0,0,0,.28);
        }
        .auth-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #1e4976, #1d6fa6);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
        }
        h1 { font-size: 24px; color: #0a1628; margin-bottom: 8px; }
        p { font-size: 13.5px; color: #64748b; line-height: 1.7; margin-bottom: 22px; }
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 7px; }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
        }
        .form-control {
            width: 100%;
            padding: 12px 13px 12px 40px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font: inherit;
            font-size: 14px;
            color: #0f172a;
            background: #f8fafc;
        }
        .form-control:focus {
            outline: none;
            border-color: #1d6fa6;
            box-shadow: 0 0 0 3px rgba(29,111,166,.12);
            background: #fff;
        }
        .btn-primary {
            width: 100%;
            border: 0;
            border-radius: 8px;
            padding: 13px;
            background: linear-gradient(135deg, #1e4976, #1d6fa6);
            color: #fff;
            font: inherit;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-primary:hover { background: linear-gradient(135deg, #163357, #1e4976); }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-top: 18px;
            color: #1d6fa6;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
        }
        .back-link:hover { text-decoration: underline; }
        .alert {
            display: flex;
            gap: 9px;
            align-items: flex-start;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 12.5px;
            line-height: 1.6;
            margin-bottom: 18px;
        }
        .alert-success { background: #ecfdf5; border: 1.5px solid #a7f3d0; color: #065f46; }
        .alert-error { background: #fef2f2; border: 1.5px solid #fecaca; color: #7f1d1d; }
        .note {
            margin-top: 18px;
            padding: 13px 14px;
            border-radius: 8px;
            background: #fffbeb;
            border: 1.5px solid #fde68a;
            color: #78350f;
            font-size: 11.5px;
            line-height: 1.65;
        }
    </style>
</head>
<body>
    <main class="auth-card">
        <div class="auth-icon"><i class="fas fa-key"></i></div>
        <h1>Lupa Password</h1>
        <p>Masukkan username atau email akun. Permintaan akan dicatat untuk diverifikasi oleh Super Admin.</p>

        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-circle-check"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-circle-xmark"></i>
                <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Username atau Email</label>
                <div class="input-wrap">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" name="identifier" class="form-control" value="{{ old('identifier') }}" required autofocus placeholder="contoh: admin_opd atau nama@instansi.go.id">
                </div>
            </div>

            <button type="submit" class="btn-primary">
                <i class="fas fa-paper-plane"></i> Kirim Permintaan
            </button>
        </form>

        <a href="{{ route('login') }}" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke login
        </a>

        <div class="note">
            Untuk keamanan, reset password dilakukan oleh Super Admin setelah identitas akun diverifikasi.
        </div>
    </main>
</body>
</html>
