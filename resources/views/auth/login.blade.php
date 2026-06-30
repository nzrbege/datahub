<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Aplikasi Berbagi Data</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #0a1628;
        }

        /* ─── Left decorative panel ─── */
        .login-left {
            flex: 1;
            background: linear-gradient(160deg, #0a1628 0%, #0f2040 40%, #163357 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            padding: 60px;
            position: relative;
            overflow: hidden;
        }
        .login-left::before {
            content: '';
            position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%231e4976' fill-opacity='0.15'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .login-left-glow {
            position: absolute; bottom: -120px; right: -100px;
            width: 500px; height: 500px; border-radius: 50%;
            background: radial-gradient(circle, rgba(59,142,199,.18) 0%, transparent 70%);
        }
        .login-left-glow2 {
            position: absolute; top: -80px; left: -80px;
            width: 350px; height: 350px; border-radius: 50%;
            background: radial-gradient(circle, rgba(245,158,11,.1) 0%, transparent 70%);
        }

        .login-logo {
            position: relative; z-index: 1;
            display: flex; align-items: center; gap: 14px;
            margin-bottom: 48px;
        }
        .login-logo-icon {
            width: 52px; height: 52px; border-radius: 14px;
            background: linear-gradient(135deg, #3b8ec7, #f59e0b);
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; color: #fff;
            box-shadow: 0 8px 24px rgba(245,158,11,.35);
        }
        .login-logo-text h1 { font-size: 18px; font-weight: 800; color: #fff; }
        .login-logo-text p  { font-size: 12px; color: rgba(255,255,255,.45); margin-top: 2px; }

        .login-headline {
            position: relative; z-index: 1;
        }
        .login-headline h2 {
            font-size: 38px; font-weight: 800; color: #fff;
            line-height: 1.1; letter-spacing: -1px;
            margin-bottom: 18px;
        }
        .login-headline h2 span {
            background: linear-gradient(90deg, #f59e0b, #fcd34d);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .login-headline p {
            font-size: 14.5px; color: rgba(255,255,255,.5); line-height: 1.7;
            max-width: 380px;
        }

        .login-features {
            position: relative; z-index: 1;
            margin-top: 48px; display: flex; flex-direction: column; gap: 14px;
        }
        .feature-item {
            display: flex; align-items: center; gap: 12px;
            font-size: 13px; color: rgba(255,255,255,.65);
        }
        .feature-icon {
            width: 32px; height: 32px; border-radius: 8px;
            background: rgba(255,255,255,.08); display: flex;
            align-items: center; justify-content: center;
            font-size: 13px; color: rgba(255,255,255,.6); flex-shrink: 0;
        }

        .login-left-footer {
            position: absolute; bottom: 28px; left: 60px;
            font-size: 11px; color: rgba(255,255,255,.2); z-index: 1;
        }

        /* ─── Right form panel ─── */
        .login-right {
            width: 460px;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 52px 48px;
            position: relative;
        }
        .login-right::before {
            content: '';
            position: absolute; top: 0; left: 0; bottom: 0; width: 1px;
            background: linear-gradient(180deg, transparent, rgba(0,0,0,.08), transparent);
        }

        .form-header { margin-bottom: 32px; }
        .form-header h3 { font-size: 24px; font-weight: 800; color: #0a1628; letter-spacing: -.5px; }
        .form-header p  { font-size: 13.5px; color: #64748b; margin-top: 6px; line-height: 1.6; }

        .form-group { margin-bottom: 18px; }
        .form-label {
            display: flex; align-items: center; gap: 6px;
            font-size: 12.5px; font-weight: 600; margin-bottom: 6px; color: #334155;
        }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; font-size: 14px; pointer-events: none;
        }
        .password-toggle {
            position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
            width: 32px; height: 32px; border: none; border-radius: 8px;
            background: transparent; color: #64748b; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
        }
        .password-toggle:hover { background: #e2e8f0; color: #0f172a; }
        .form-control {
            width: 100%; padding: 11px 13px 11px 40px;
            border: 1.5px solid #e2e8f0; border-radius: 8px;
            font-size: 14px; font-family: inherit; color: #0f172a;
            background: #f8fafc;
            transition: border-color .15s, box-shadow .15s, background .15s;
        }
        .form-control:focus {
            outline: none; border-color: #1d6fa6;
            box-shadow: 0 0 0 3px rgba(29,111,166,.12);
            background: #fff;
        }
        .form-control::placeholder { color: #cbd5e1; }
        .input-wrap.has-toggle .form-control { padding-right: 48px; }

        .btn-login {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, #1e4976, #1d6fa6);
            color: #fff; border: none; border-radius: 8px;
            font-size: 14px; font-weight: 700; cursor: pointer;
            font-family: inherit; margin-top: 4px;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: all .15s;
            box-shadow: 0 4px 14px rgba(29,111,166,.35);
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #163357, #1e4976);
            box-shadow: 0 6px 20px rgba(29,111,166,.45);
            transform: translateY(-1px);
        }
        .btn-login:active { transform: translateY(0); }

        .error-box {
            background: #fef2f2; border: 1.5px solid #fecaca;
            border-radius: 8px; padding: 12px 15px;
            font-size: 12.5px; color: #7f1d1d; margin-bottom: 18px;
            display: flex; gap: 9px; align-items: flex-start;
        }
        .error-box i { color: #dc2626; margin-top: 1px; flex-shrink: 0; }

        .pdp-box {
            margin-top: 22px; padding: 14px 16px;
            background: #fffbeb; border: 1.5px solid #fde68a;
            border-radius: 8px; font-size: 11.5px; color: #78350f;
            line-height: 1.65;
        }
        .pdp-box strong { color: #b45309; }

        .divider {
            display: flex; align-items: center; gap: 10px;
            margin: 20px 0 4px;
            font-size: 11.5px; color: #94a3b8; font-weight: 600;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1; height: 1px; background: #e2e8f0;
        }

        .checkbox-row {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; color: #475569; cursor: pointer;
        }
        .checkbox-row input { accent-color: #1d6fa6; width: 15px; height: 15px; cursor: pointer; }
        .login-options {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            margin-bottom: 20px;
        }
        .forgot-link {
            font-size: 12.5px; font-weight: 700; color: #1d6fa6; text-decoration: none;
        }
        .forgot-link:hover { color: #163357; text-decoration: underline; }

        .login-footer {
            margin-top: 28px; text-align: center;
            font-size: 11.5px; color: #94a3b8;
        }

        @media (max-width: 900px) {
            .login-left { display: none; }
            .login-right { width: 100%; padding: 40px 28px; }
        }
    </style>
</head>
<body>
    <!-- Left panel -->
    <div class="login-left">
        <div class="login-left-glow"></div>
        <div class="login-left-glow2"></div>

        <div class="login-logo">
            <div class="login-logo-icon"><i class="fas fa-landmark"></i></div>
            <div class="login-logo-text">
                <h1>Aplikasi Berbagi Data</h1>
                <p>Portal pertukaran data antar instansi</p>
            </div>
        </div>

        <div class="login-headline">
            <h2>Berbagi Data<br>dengan Aman<br><span>dan Terkelola</span></h2>
            <p>Aplikasi berbagi data antar instansi dengan kontrol akses, lampiran NDA, dan pencatatan aktivitas.</p>
        </div>

        <div class="login-features">
            <div class="feature-item">
                <div class="feature-icon"><i class="fas fa-lock"></i></div>
                File dataset disimpan di storage privat
            </div>
            <div class="feature-item">
                <div class="feature-icon"><i class="fas fa-clipboard-list"></i></div>
                Seluruh akses tercatat dalam audit log permanen
            </div>
            <div class="feature-item">
                <div class="feature-icon"><i class="fas fa-file-signature"></i></div>
                Akses berbasis permintaan dan dokumen NDA
            </div>
            <div class="feature-item">
                <div class="feature-icon"><i class="fas fa-shield-halved"></i></div>
                Mendukung tata kelola dan perlindungan data
            </div>
        </div>

        <div class="login-left-footer">© {{ date('Y') }} Aplikasi Berbagi Data</div>
    </div>

    <!-- Right form panel -->
    <div class="login-right">
        <div class="form-header">
            <h3>Masuk ke Sistem</h3>
            <p>Masuk dengan email untuk mengakses dataset, mengajukan permintaan, dan memantau riwayat akses data.</p>
        </div>

        @if($errors->any())
            <div class="error-box">
                <i class="fas fa-circle-xmark"></i>
                <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label class="form-label"><i class="fas fa-envelope" style="color:#94a3b8;font-size:11px"></i> Email</label>
                <div class="input-wrap">
                    <i class="fas fa-at input-icon"></i>
                    <input type="email" name="email" class="form-control"
                        value="{{ old('email') }}" required autofocus
                        placeholder="nama@opd.go.id">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label"><i class="fas fa-key" style="color:#94a3b8;font-size:11px"></i> Kata Sandi</label>
                <div class="input-wrap has-toggle">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password" class="form-control password-field"
                        required placeholder="••••••••••">
                    <button type="button" class="password-toggle" aria-label="Lihat kata sandi" title="Lihat kata sandi">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="login-options">
                <label class="checkbox-row">
                    <input type="checkbox" name="remember"> Ingat saya selama 30 hari
                </label>
                <a href="{{ route('password.request') }}" class="forgot-link">Lupa password?</a>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-right-to-bracket"></i> Masuk ke Sistem
            </button>
        </form>

        <div class="divider">Belum punya akun?</div>
        <a href="{{ route('register.request') }}" class="btn-login" style="background:#f8fafc;color:#1e4976;box-shadow:none;border:1.5px solid #cbd5e1;text-decoration:none;">
            <i class="fas fa-user-plus"></i> Ajukan Registrasi User
        </a>

        <div class="pdp-box">
            <strong><i class="fas fa-triangle-exclamation" style="color:#d97706;"></i> Peringatan Keamanan Data:</strong><br>
            Sistem ini mengelola data yang dilindungi ketentuan perlindungan data. Seluruh aktivitas dipantau dan dicatat untuk keamanan.
        </div>

        <div class="login-footer">
            © {{ date('Y') }} Aplikasi Berbagi Data &mdash; v2.0
        </div>
    </div>
    <script>
    document.querySelectorAll('.password-toggle').forEach(function(button) {
        button.addEventListener('click', function() {
            const input = this.closest('.input-wrap').querySelector('.password-field');
            const icon = this.querySelector('i');
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            icon.classList.toggle('fa-eye', !isHidden);
            icon.classList.toggle('fa-eye-slash', isHidden);
            this.setAttribute('aria-label', isHidden ? 'Sembunyikan kata sandi' : 'Lihat kata sandi');
            this.setAttribute('title', isHidden ? 'Sembunyikan kata sandi' : 'Lihat kata sandi');
        });
    });
    </script>
</body>
</html>
