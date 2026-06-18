<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Akses Ditolak</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
            background: linear-gradient(160deg, #0a1628 0%, #0f2040 50%, #163357 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        .error-container {
            text-align: center;
            padding: 48px 40px;
            max-width: 460px;
        }
        .error-icon {
            width: 90px; height: 90px; border-radius: 50%;
            background: rgba(220,38,38,.15);
            border: 2px solid rgba(220,38,38,.3);
            display: flex; align-items: center; justify-content: center;
            font-size: 36px; margin: 0 auto 28px;
        }
        .error-code {
            font-size: 72px; font-weight: 800; line-height: 1;
            background: linear-gradient(90deg, #f87171, #fca5a5);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; margin-bottom: 14px;
        }
        .error-title { font-size: 22px; font-weight: 700; margin-bottom: 12px; }
        .error-desc { font-size: 14px; color: rgba(255,255,255,.55); line-height: 1.7; margin-bottom: 32px; }
        .audit-note {
            background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1);
            border-radius: 10px; padding: 13px 16px;
            font-size: 12px; color: rgba(255,255,255,.45);
            margin-bottom: 28px; text-align: left;
        }
        .audit-note strong { color: rgba(255,255,255,.65); }
        .btn-back {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,.1); color: #fff;
            border: 1.5px solid rgba(255,255,255,.2);
            padding: 11px 24px; border-radius: 8px;
            font-size: 14px; font-weight: 600; text-decoration: none;
            transition: all .15s;
        }
        .btn-back:hover { background: rgba(255,255,255,.18); border-color: rgba(255,255,255,.35); }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">🔒</div>
        <div class="error-code">403</div>
        <div class="error-title">Akses Ditolak</div>
        <div class="error-desc">Anda tidak memiliki izin untuk mengakses halaman atau sumber daya ini. Pastikan Anda telah masuk dengan akun yang tepat.</div>
        <div class="audit-note">
            <strong>⚠️ Catatan Keamanan:</strong><br>
            Upaya akses tidak sah ini telah dicatat dalam sistem audit sesuai UU PDP No.27/2022. Jika Anda merasa ini adalah kesalahan, hubungi administrator sistem.
        </div>
        <a href="{{ url()->previous() }}" class="btn-back">
            ← Kembali ke Halaman Sebelumnya
        </a>
    </div>
</body>
</html>
