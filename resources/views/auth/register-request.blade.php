<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi User - Aplikasi Berbagi Data</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px;
        }
        .page {
            width: min(960px, 100%);
            display: grid;
            grid-template-columns: .9fr 1.1fr;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 16px 48px rgba(15,23,42,.12);
            overflow: hidden;
        }
        .intro {
            background: linear-gradient(160deg, #0a1628, #163357);
            color: #fff;
            padding: 42px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 38px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #3b8ec7, #f59e0b);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(245,158,11,.32);
        }
        .brand strong { display: block; font-size: 15px; }
        .brand span { display: block; font-size: 11.5px; color: rgba(255,255,255,.55); margin-top: 2px; }
        .intro h1 {
            font-size: 32px;
            line-height: 1.14;
            letter-spacing: -.6px;
            margin-bottom: 14px;
        }
        .intro p {
            color: rgba(255,255,255,.65);
            font-size: 13.5px;
            line-height: 1.75;
        }
        .intro-note {
            border: 1px solid rgba(255,255,255,.12);
            background: rgba(255,255,255,.08);
            border-radius: 10px;
            padding: 14px;
            font-size: 12.5px;
            line-height: 1.65;
            color: rgba(255,255,255,.72);
        }
        .form-panel {
            padding: 38px;
        }
        .form-head {
            margin-bottom: 24px;
        }
        .form-head h2 {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -.4px;
        }
        .form-head p {
            color: #64748b;
            font-size: 13px;
            line-height: 1.6;
            margin-top: 6px;
        }
        .alert {
            display: flex;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 12.5px;
            line-height: 1.55;
            margin-bottom: 18px;
        }
        .alert-success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
        .alert-danger { background: #fef2f2; border: 1px solid #fecaca; color: #7f1d1d; }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .form-group.full { grid-column: 1 / -1; }
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 6px;
        }
        .form-control {
            width: 100%;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
            color: #0f172a;
            padding: 11px 12px;
            font: inherit;
            font-size: 13.5px;
        }
        .form-control:focus {
            outline: none;
            border-color: #1d6fa6;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(29,111,166,.12);
        }
        .opd-combobox {
            position: relative;
        }
        .opd-input-wrap {
            position: relative;
        }
        .opd-input-wrap .form-control {
            padding-right: 42px;
        }
        .opd-toggle {
            position: absolute;
            top: 50%;
            right: 8px;
            width: 30px;
            height: 30px;
            border: 0;
            border-radius: 7px;
            background: transparent;
            color: #64748b;
            transform: translateY(-50%);
            cursor: pointer;
        }
        .opd-toggle:hover {
            background: #e2e8f0;
            color: #1e4976;
        }
        .opd-panel {
            position: absolute;
            z-index: 20;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            max-height: 240px;
            overflow: auto;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 16px 34px rgba(15,23,42,.16);
            padding: 6px;
        }
        .opd-panel[hidden] {
            display: none;
        }
        .opd-option {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 9px;
            border: 0;
            border-radius: 7px;
            background: transparent;
            color: #0f172a;
            padding: 9px 10px;
            font: inherit;
            font-size: 13px;
            font-weight: 600;
            text-align: left;
            cursor: pointer;
        }
        .opd-option i {
            color: #64748b;
            font-size: 11px;
        }
        .opd-option:hover,
        .opd-option.is-active {
            background: #eff6ff;
            color: #1e4976;
        }
        .opd-empty {
            padding: 12px;
            color: #64748b;
            font-size: 12.5px;
            line-height: 1.45;
        }
        .help {
            font-size: 11.5px;
            color: #64748b;
            margin-top: 6px;
            line-height: 1.5;
        }
        .invalid {
            font-size: 11.5px;
            color: #dc2626;
            margin-top: 5px;
        }
        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 22px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 8px;
            border: none;
            padding: 11px 16px;
            font: inherit;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
        }
        .btn-primary {
            color: #fff;
            background: linear-gradient(135deg, #1e4976, #1d6fa6);
            box-shadow: 0 4px 14px rgba(29,111,166,.32);
        }
        .btn-ghost {
            color: #1e4976;
            background: #f8fafc;
            border: 1.5px solid #cbd5e1;
        }
        @media (max-width: 860px) {
            body { padding: 0; align-items: stretch; }
            .page { grid-template-columns: 1fr; border-radius: 0; min-height: 100vh; }
            .intro { padding: 30px; gap: 24px; }
            .form-panel { padding: 30px; }
        }
        @media (max-width: 560px) {
            .form-grid { grid-template-columns: 1fr; }
            .actions { flex-direction: column-reverse; align-items: stretch; }
            .btn { width: 100%; }
        }
    </style>
</head>
<body>
    <main class="page">
        <section class="intro">
            <div class="brand">
                <div class="brand-icon"><i class="fas fa-landmark"></i></div>
                <div>
                    <strong>Aplikasi Berbagi Data</strong>
                    <span>Portal pertukaran data antar instansi</span>
                </div>
            </div>

            <div>
                <h1>Ajukan akses user OPD</h1>
                <p>Permohonan akan diverifikasi oleh Super Admin. Jika disetujui, akun admin OPD akan dibuat dan dapat digunakan untuk login menggunakan email.</p>
            </div>

            <div class="intro-note">
                <strong><i class="fas fa-file-pdf"></i> Surat permohonan wajib PDF.</strong><br>
                Pastikan surat berisi identitas OPD dan dasar kebutuhan akses aplikasi.
            </div>
        </section>

        <section class="form-panel">
            <div class="form-head">
                <h2>Form Registrasi User</h2>
                <p>Isi data pemohon dengan benar agar proses verifikasi dapat dilakukan lebih cepat.</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success"><i class="fas fa-circle-check"></i><div>{{ session('success') }}</div></div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-circle-xmark"></i>
                    <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
                </div>
            @endif

            <form action="{{ route('register.request.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-grid">
                    <div class="form-group full">
                        <label for="instansi" class="form-label">Instansi / OPD</label>
                        <div class="opd-combobox" data-opd-combobox>
                            <div class="opd-input-wrap">
                                <input id="instansi" type="search" name="instansi" class="form-control" value="{{ old('instansi') }}" required autofocus placeholder="Ketik nama OPD" autocomplete="off" role="combobox" aria-expanded="false" aria-controls="opd-options">
                                <button type="button" class="opd-toggle" data-opd-toggle aria-label="Tampilkan daftar OPD">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                            <div id="opd-options" class="opd-panel" role="listbox" hidden>
                                <div data-opd-list></div>
                                <div class="opd-empty" data-opd-empty hidden>OPD tidak ditemukan.</div>
                            </div>
                        </div>
                        <div class="help">Ketik untuk mencari, lalu pilih OPD dari daftar.</div>
                        @error('instansi')<div class="invalid">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="name" class="form-label">Nama</label>
                        <input id="name" type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input id="username" type="text" name="username" class="form-control" value="{{ old('username') }}" required>
                        <div class="help">Gunakan huruf, angka, titik, garis bawah, atau strip.</div>
                        @error('username')<div class="invalid">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="phone" class="form-label">No HP</label>
                        <input id="phone" type="text" name="phone" class="form-control" value="{{ old('phone') }}" required>
                        @error('phone')<div class="invalid">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        @error('email')<div class="invalid">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group full">
                        <label for="letter_file" class="form-label">Surat Permohonan</label>
                        <input id="letter_file" type="file" name="letter_file" class="form-control" accept=".pdf,application/pdf" required>
                        <div class="help">Upload file PDF maksimal 10MB.</div>
                        @error('letter_file')<div class="invalid">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="actions">
                    <a href="{{ route('login') }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Kembali Login</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Kirim Permohonan</button>
                </div>
            </form>
        </section>
    </main>
    <script>
        const combobox = document.querySelector('[data-opd-combobox]');
        if (combobox) {
            const opdOptions = @json($opdOptions->values());
            const input = combobox.querySelector('#instansi');
            const toggle = combobox.querySelector('[data-opd-toggle]');
            const panel = combobox.querySelector('#opd-options');
            const list = combobox.querySelector('[data-opd-list]');
            const empty = combobox.querySelector('[data-opd-empty]');
            let renderedOptions = [];
            let activeIndex = -1;

            const openPanel = () => {
                panel.hidden = false;
                input.setAttribute('aria-expanded', 'true');
            };

            const closePanel = () => {
                panel.hidden = true;
                input.setAttribute('aria-expanded', 'false');
                activeIndex = -1;
                renderedOptions.forEach(option => option.classList.remove('is-active'));
            };

            const normalize = value => value.toLowerCase().replace(/\s+/g, ' ').trim();

            const chooseOption = value => {
                input.value = value;
                closePanel();
                input.focus();
            };

            const renderOptions = matches => {
                list.innerHTML = '';
                renderedOptions = matches.slice(0, 80).map(opd => {
                    const option = document.createElement('button');
                    const icon = document.createElement('i');
                    const label = document.createElement('span');

                    option.type = 'button';
                    option.className = 'opd-option';
                    option.setAttribute('role', 'option');
                    icon.className = 'fas fa-building';
                    label.textContent = opd;

                    option.append(icon, label);
                    option.addEventListener('mousedown', event => event.preventDefault());
                    option.addEventListener('click', () => chooseOption(opd));
                    list.appendChild(option);

                    return option;
                });

                empty.hidden = matches.length > 0;
            };

            const filterOptions = () => {
                const query = normalize(input.value);
                const matches = query
                    ? opdOptions.filter(opd => normalize(opd).includes(query))
                    : opdOptions;

                renderOptions(matches);
                activeIndex = -1;
                openPanel();
            };

            input.addEventListener('focus', filterOptions);
            input.addEventListener('input', filterOptions);

            toggle.addEventListener('mousedown', event => event.preventDefault());
            toggle.addEventListener('click', () => {
                if (panel.hidden) {
                    filterOptions();
                    input.focus();
                } else {
                    closePanel();
                }
            });

            input.addEventListener('keydown', event => {
                const currentOptions = renderedOptions;
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    if (panel.hidden) filterOptions();
                    activeIndex = Math.min(activeIndex + 1, currentOptions.length - 1);
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    activeIndex = Math.max(activeIndex - 1, 0);
                } else if (event.key === 'Enter' && activeIndex >= 0 && currentOptions[activeIndex]) {
                    event.preventDefault();
                    chooseOption(currentOptions[activeIndex].querySelector('span').textContent);
                    return;
                } else if (event.key === 'Escape') {
                    closePanel();
                    return;
                } else {
                    return;
                }

                renderedOptions.forEach(option => option.classList.remove('is-active'));
                if (currentOptions[activeIndex]) {
                    currentOptions[activeIndex].classList.add('is-active');
                    currentOptions[activeIndex].scrollIntoView({ block: 'nearest' });
                }
            });

            document.addEventListener('click', event => {
                if (!combobox.contains(event.target)) closePanel();
            });
        }
    </script>
</body>
</html>
