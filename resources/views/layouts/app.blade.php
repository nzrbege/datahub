<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Aplikasi Berbagi Data')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            /* Brand palette — deep teal government, energised with amber accent */
            --brand-900: #0a1628;
            --brand-800: #0f2040;
            --brand-700: #163357;
            --brand-600: #1e4976;
            --brand-500: #1d6fa6;
            --brand-400: #3b8ec7;
            --brand-300: #6eb3de;
            --brand-200: #b3d9f0;
            --brand-100: #e5f3fb;
            --brand-50:  #f0f8fd;

            --accent:       #f59e0b;
            --accent-light: #fde68a;
            --accent-dark:  #b45309;

            --success-bg:  #ecfdf5; --success:  #059669; --success-border: #a7f3d0;
            --warning-bg:  #fffbeb; --warning:  #d97706; --warning-border: #fde68a;
            --danger-bg:   #fef2f2; --danger:   #dc2626; --danger-border:  #fecaca;
            --info-bg:     #eff6ff; --info:     #2563eb; --info-border:    #bfdbfe;

            --surface:     #ffffff;
            --surface-2:   #f8fafc;
            --surface-3:   #f1f5f9;
            --border:      #e2e8f0;
            --border-dark: #cbd5e1;
            --text:        #0f172a;
            --text-2:      #334155;
            --text-muted:  #64748b;
            --text-ghost:  #94a3b8;

            --sidebar-w: 268px;
            --topbar-h:  64px;
            --radius:    12px;
            --radius-sm: 8px;
            --radius-xs: 6px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
            --shadow:    0 4px 16px rgba(0,0,0,.06), 0 1px 4px rgba(0,0,0,.04);
            --shadow-lg: 0 12px 40px rgba(0,0,0,.10), 0 2px 8px rgba(0,0,0,.05);
        }

        html { scroll-behavior: smooth; }
        body {
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
            font-size: 14px;
            line-height: 1.6;
            background: var(--surface-2);
            color: var(--text);
            display: flex;
            min-height: 100vh;
        }

        /* ─── SCROLLBAR ─── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border-dark); border-radius: 99px; }

        /* ══════════════ SIDEBAR ══════════════ */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--brand-900);
            background-image: linear-gradient(160deg, var(--brand-900) 0%, #0d1f3c 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; bottom: 0; left: 0;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 100;
            border-right: 1px solid rgba(255,255,255,.06);
        }

        .sidebar-brand {
            padding: 22px 20px 18px;
            border-bottom: 1px solid rgba(255,255,255,.08);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-brand-icon {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--brand-400), var(--accent));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(245,158,11,.35);
        }
        .sidebar-brand-text h1 {
            font-size: 14px; font-weight: 800; line-height: 1.2;
            letter-spacing: -.2px;
        }
        .sidebar-brand-text p {
            font-size: 10.5px; color: rgba(255,255,255,.45); margin-top: 1px;
            font-weight: 500;
        }

        .sidebar-user {
            padding: 14px 20px;
            border-bottom: 1px solid rgba(255,255,255,.07);
            display: flex; align-items: center; gap: 11px;
        }
        .user-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, var(--brand-500), var(--brand-300));
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 700; flex-shrink: 0;
            text-transform: uppercase;
        }
        .user-info { flex: 1; min-width: 0; }
        .user-info strong {
            display: block; font-size: 13px; font-weight: 700;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .user-info span {
            font-size: 11px; color: rgba(255,255,255,.45);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;
        }
        .role-pill {
            display: inline-flex; align-items: center; gap: 4px;
            background: rgba(245,158,11,.18); color: #fde68a;
            padding: 2px 8px; border-radius: 999px;
            font-size: 10px; font-weight: 600; margin-top: 3px;
            border: 1px solid rgba(245,158,11,.25);
        }
        .role-pill.admin { background: rgba(59,142,199,.18); color: #93c5fd; border-color: rgba(59,142,199,.25); }

        .sidebar nav { flex: 1; padding: 10px 12px; }
        .nav-label {
            font-size: 9.5px; font-weight: 700; letter-spacing: 1.2px;
            text-transform: uppercase; color: rgba(255,255,255,.3);
            padding: 14px 8px 5px;
        }
        .nav-link {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 12px; border-radius: var(--radius-sm);
            color: rgba(255,255,255,.62); text-decoration: none;
            font-size: 13px; font-weight: 500;
            transition: background .15s, color .15s;
            margin-bottom: 2px; cursor: pointer;
            position: relative;
        }
        .nav-link:hover { background: rgba(255,255,255,.08); color: rgba(255,255,255,.9); }
        .nav-link.active {
            background: linear-gradient(90deg, rgba(59,142,199,.22), rgba(59,142,199,.08));
            color: #fff;
            border-left: 3px solid var(--brand-300);
            padding-left: 9px;
        }
        .nav-link.active::after {
            content: '';
            position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
            width: 5px; height: 5px; border-radius: 50%;
            background: var(--brand-300);
        }
        .nav-link.has-notification.active::after { right: 44px; }
        .nav-link i { width: 16px; text-align: center; font-size: 13px; opacity: .8; }
        .nav-badge {
            margin-left: auto; background: var(--accent); color: #fff;
            min-width: 18px; text-align: center;
            font-size: 9px; font-weight: 800; padding: 2px 6px; border-radius: 999px;
            box-shadow: 0 0 0 2px rgba(10,22,40,.9);
        }

        .sidebar-footer {
            padding: 14px 20px;
            border-top: 1px solid rgba(255,255,255,.07);
            font-size: 11px; color: rgba(255,255,255,.3);
        }
        .sidebar-footer a {
            color: rgba(255,255,255,.45); text-decoration: none;
            display: inline-flex; align-items: center; gap: 6px;
            transition: color .15s;
        }
        .sidebar-footer a:hover { color: rgba(255,255,255,.8); }
        .sidebar-clock { font-family: 'JetBrains Mono', monospace; margin-bottom: 8px; font-size: 11px; }

        /* ══════════════ MAIN CONTENT ══════════════ */
        .main-wrap {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar {
            height: var(--topbar-h);
            background: rgba(255,255,255,.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            padding: 0 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky; top: 0; z-index: 50;
        }
        .topbar-left { display: flex; align-items: center; gap: 10px; }
        .page-eyebrow { font-size: 11px; font-weight: 600; color: var(--brand-500); text-transform: uppercase; letter-spacing: .6px; }
        .page-title { font-size: 18px; font-weight: 800; color: var(--text); letter-spacing: -.3px; }
        .topbar-right {
            display: flex; align-items: center; gap: 8px;
        }
        .topbar-badge {
            display: flex; align-items: center; gap: 6px;
            padding: 6px 12px; border-radius: var(--radius-sm);
            font-size: 12px; font-weight: 600;
            background: var(--success-bg); color: var(--success);
            border: 1px solid var(--success-border);
        }
        .topbar-sep { width: 1px; height: 20px; background: var(--border); margin: 0 4px; }
        .topbar-user {
            display: flex; align-items: center; gap: 8px;
            padding: 6px 12px; border-radius: var(--radius-sm);
            background: var(--surface-3); font-size: 12px; font-weight: 600;
            color: var(--text-2);
        }
        .topbar-user i { color: var(--brand-500); }

        .main-content {
            padding: 28px;
            flex: 1;
        }

        /* ══════════════ PDP NOTICE ══════════════ */
        .pdp-notice {
            display: flex; align-items: flex-start; gap: 12px;
            background: linear-gradient(90deg, #fffbf0, #fffdf8);
            border: 1px solid #fde68a;
            border-left: 4px solid var(--accent);
            border-radius: var(--radius);
            padding: 14px 18px;
            font-size: 12.5px;
            margin-bottom: 24px;
            color: #78350f;
        }
        .pdp-notice i { color: var(--accent); margin-top: 1px; flex-shrink: 0; }
        .pdp-notice strong { color: var(--accent-dark); }

        /* ══════════════ ALERTS ══════════════ */
        .alert {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 13px 16px; border-radius: var(--radius);
            font-size: 13px; margin-bottom: 16px; border: 1px solid transparent;
        }
        .alert i { margin-top: 1px; flex-shrink: 0; }
        .alert-success { background: var(--success-bg); color: #065f46; border-color: var(--success-border); }
        .alert-success i { color: var(--success); }
        .alert-danger  { background: var(--danger-bg);  color: #7f1d1d; border-color: var(--danger-border); }
        .alert-danger  i { color: var(--danger); }
        .alert-warning { background: var(--warning-bg); color: #7c2d12; border-color: var(--warning-border); }
        .alert-warning i { color: var(--warning); }
        .alert-info    { background: var(--info-bg);    color: #1e3a8a; border-color: var(--info-border); }
        .alert-info    i { color: var(--info); }

        /* ══════════════ STAT CARDS ══════════════ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
            transition: transform .2s, box-shadow .2s;
            color: inherit;
            text-decoration: none;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow); }
        .stat-card::after {
            content: ''; position: absolute;
            top: 0; left: 0; right: 0; height: 3px;
        }
        .stat-card.blue::after  { background: linear-gradient(90deg, var(--brand-500), var(--brand-300)); }
        .stat-card.orange::after{ background: linear-gradient(90deg, #f59e0b, #fcd34d); }
        .stat-card.green::after { background: linear-gradient(90deg, #059669, #34d399); }
        .stat-card.red::after   { background: linear-gradient(90deg, #dc2626, #f87171); }

        .stat-top { display: flex; align-items: flex-start; justify-content: space-between; }
        .stat-icon {
            width: 44px; height: 44px; border-radius: var(--radius-sm);
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
        }
        .stat-icon.blue   { background: var(--brand-100); color: var(--brand-600); }
        .stat-icon.orange { background: #fef3c7; color: #b45309; }
        .stat-icon.green  { background: #d1fae5; color: #065f46; }
        .stat-icon.red    { background: #fee2e2; color: #991b1b; }
        .stat-trend { font-size: 11px; font-weight: 600; color: var(--success); }
        .stat-num { font-size: 30px; font-weight: 800; margin-top: 12px; letter-spacing: -1px; line-height: 1; }
        .stat-label { font-size: 12px; color: var(--text-muted); margin-top: 4px; font-weight: 500; }

        /* ══════════════ CARDS ══════════════ */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            margin-bottom: 20px;
        }
        .card-header {
            display: flex; justify-content: space-between; align-items: center;
            padding: 18px 22px 16px;
            border-bottom: 1px solid var(--border);
            background: var(--surface);
        }
        .card-title {
            font-size: 14px; font-weight: 700; color: var(--text);
            display: flex; align-items: center; gap: 8px;
        }
        .card-title i { color: var(--brand-500); }
        .card-body { padding: 22px; }

        /* ══════════════ TABLES ══════════════ */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        thead tr { background: var(--surface-2); }
        th {
            padding: 11px 16px; text-align: left;
            font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px;
            color: var(--text-muted); white-space: nowrap;
            border-bottom: 2px solid var(--border);
        }
        td {
            padding: 13px 16px; border-bottom: 1px solid var(--border);
            vertical-align: middle; color: var(--text-2);
        }
        tbody tr { transition: background .12s; }
        tbody tr:hover td { background: var(--brand-50); }
        tbody tr:last-child td { border-bottom: none; }

        /* ══════════════ BADGES ══════════════ */
        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 10px; border-radius: 999px;
            font-size: 11px; font-weight: 700; white-space: nowrap;
        }
        .badge::before { content: ''; width: 5px; height: 5px; border-radius: 50%; }
        .badge-pending  { background: #fef9c3; color: #713f12; }
        .badge-pending::before { background: #ca8a04; }
        .badge-returned  { background: #fffbeb; color: #92400e; }
        .badge-returned::before { background: #f59e0b; }
        .badge-blue, .badge-approved { background: #dbeafe; color: #1e3a8a; }
        .badge-blue::before, .badge-approved::before { background: #2563eb; }
        .badge-bast_pending { background: #fef9c3; color: #713f12; }
        .badge-bast_pending::before { background: #ca8a04; }
        .badge-bast_approved { background: #dcfce7; color: #14532d; }
        .badge-bast_approved::before { background: #16a34a; }
        .badge-bast_rejected { background: #fee2e2; color: #7f1d1d; }
        .badge-bast_rejected::before { background: #dc2626; }
        .badge-rejected { background: #fee2e2; color: #7f1d1d; }
        .badge-rejected::before { background: #dc2626; }
        .badge-revoked  { background: var(--surface-3); color: var(--text-muted); }
        .badge-revoked::before { background: var(--text-ghost); }
        .badge-active   { background: #dcfce7; color: #14532d; }
        .badge-active::before { background: #16a34a; }
        .badge-inactive { background: #fee2e2; color: #7f1d1d; }
        .badge-inactive::before { background: #dc2626; }
        .badge-sa       { background: #ede9fe; color: #4c1d95; }
        .badge-sa::before { background: #7c3aed; }
        .badge-admin    { background: var(--brand-100); color: var(--brand-700); }
        .badge-admin::before { background: var(--brand-500); }
        .badge-cat { background: var(--brand-100); color: var(--brand-700); border-radius: var(--radius-xs); padding: 2px 8px; }
        .badge-cat::before { display: none; }

        /* ══════════════ BUTTONS ══════════════ */
        .btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 9px 18px; border-radius: var(--radius-sm);
            border: none; cursor: pointer; font-size: 13px; font-weight: 600;
            text-decoration: none; transition: all .15s;
            font-family: inherit; letter-spacing: .1px; white-space: nowrap;
        }
        .btn:active { transform: scale(.97); }
        .btn-primary {
            background: linear-gradient(135deg, var(--brand-600), var(--brand-500));
            color: #fff;
            box-shadow: 0 2px 8px rgba(30,73,118,.3);
        }
        .btn-primary:hover { background: linear-gradient(135deg, var(--brand-700), var(--brand-600)); box-shadow: 0 4px 12px rgba(30,73,118,.4); }
        .btn-success {
            background: linear-gradient(135deg, #059669, #10b981);
            color: #fff; box-shadow: 0 2px 8px rgba(5,150,105,.25);
        }
        .btn-success:hover { background: linear-gradient(135deg, #047857, #059669); }
        .btn-danger {
            background: linear-gradient(135deg, #dc2626, #ef4444);
            color: #fff; box-shadow: 0 2px 8px rgba(220,38,38,.25);
        }
        .btn-danger:hover { background: linear-gradient(135deg, #b91c1c, #dc2626); }
        .btn-warning {
            background: linear-gradient(135deg, #d97706, #f59e0b);
            color: #fff; box-shadow: 0 2px 8px rgba(217,119,6,.25);
        }
        .btn-warning:hover { background: linear-gradient(135deg, #b45309, #d97706); }
        .btn-outline {
            background: transparent; color: var(--brand-600);
            border: 1.5px solid var(--brand-300);
        }
        .btn-outline:hover { background: var(--brand-100); }
        .btn-ghost {
            background: transparent; color: var(--text-muted);
            border: 1.5px solid var(--border);
        }
        .btn-ghost:hover { background: var(--surface-3); color: var(--text); }
        .btn-sm { padding: 6px 12px; font-size: 12px; gap: 5px; }
        .btn-xs { padding: 4px 8px; font-size: 11px; gap: 4px; border-radius: 6px; }

        /* ══════════════ FORMS ══════════════ */
        .form-group { margin-bottom: 18px; }
        .form-label {
            display: flex; align-items: center; gap: 6px;
            font-size: 12.5px; font-weight: 600; margin-bottom: 6px; color: var(--text-2);
        }
        .form-label .required { color: var(--danger); }
        .form-control {
            width: 100%; padding: 9px 13px;
            border: 1.5px solid var(--border); border-radius: var(--radius-sm);
            font-size: 13px; font-family: inherit; color: var(--text);
            background: var(--surface);
            transition: border-color .15s, box-shadow .15s;
        }
        .form-control:focus {
            outline: none; border-color: var(--brand-400);
            box-shadow: 0 0 0 3px rgba(59,142,199,.15);
        }
        .form-control::placeholder { color: var(--text-ghost); }
        .form-text { font-size: 11.5px; color: var(--text-muted); margin-top: 5px; }
        .invalid-feedback { color: var(--danger); font-size: 12px; margin-top: 4px; font-weight: 500; }
        textarea.form-control { resize: vertical; min-height: 96px; }
        select.form-control { cursor: pointer; }
        .checkbox-wrap { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 500; cursor: pointer; }
        .checkbox-wrap input[type="checkbox"] { width: 15px; height: 15px; accent-color: var(--brand-500); cursor: pointer; }

        /* ══════════════ LAYOUT HELPERS ══════════════ */
        .section-grid {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(300px, 1fr);
            gap: 20px;
            align-items: start;
        }
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start; }
        .detail-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; align-items: start; }

        /* ══════════════ DATASET CARDS ══════════════ */
        .dataset-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 14px;
            padding: 0;
        }
        .dataset-card {
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            padding: 18px;
            background: var(--surface);
            display: flex; flex-direction: column; gap: 12px;
            transition: all .2s; cursor: default;
        }
        .dataset-card:hover { border-color: var(--brand-300); box-shadow: var(--shadow); transform: translateY(-2px); }
        .dataset-card__header { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
        .dataset-card__icon {
            width: 36px; height: 36px; border-radius: var(--radius-sm);
            background: var(--brand-100); color: var(--brand-600);
            display: flex; align-items: center; justify-content: center;
            font-size: 15px; flex-shrink: 0;
        }
        .dataset-card__title { font-size: 13.5px; font-weight: 700; line-height: 1.35; color: var(--text); }
        .dataset-card__sub { font-size: 11px; color: var(--text-ghost); margin-top: 2px; font-family: 'JetBrains Mono', monospace; }
        .dataset-meta { display: flex; flex-wrap: wrap; gap: 6px; }
        .dataset-meta-chip {
            font-size: 11px; font-weight: 600;
            background: var(--surface-3); color: var(--text-muted);
            padding: 3px 8px; border-radius: 999px;
            border: 1px solid var(--border);
        }
        .dataset-desc { font-size: 12px; color: var(--text-muted); line-height: 1.6; flex: 1; }
        .dataset-card__actions { display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-top: 2px; }

        /* ══════════════ QUICK LIST ══════════════ */
        .quick-list { display: flex; flex-direction: column; }
        .quick-item {
            display: flex; justify-content: space-between; align-items: center;
            gap: 12px; padding: 13px 0;
            border-bottom: 1px solid var(--border);
        }
        .quick-item:last-child { border-bottom: none; }
        .quick-item-info strong { font-size: 13px; font-weight: 600; }
        .quick-item-info span  { font-size: 11.5px; color: var(--text-muted); display: block; margin-top: 2px; }

        /* ══════════════ STEP FLOW ══════════════ */
        .flow-steps { display: flex; flex-direction: column; gap: 0; }
        .flow-step {
            display: flex; align-items: flex-start; gap: 14px;
            padding: 12px 0; position: relative;
        }
        .flow-step:not(:last-child)::after {
            content: ''; position: absolute; left: 16px; top: 40px; bottom: -4px;
            width: 2px; background: var(--border); z-index: 0;
        }
        .flow-num {
            width: 32px; height: 32px; border-radius: 50%;
            background: var(--brand-100); color: var(--brand-700);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 800; flex-shrink: 0; z-index: 1;
            border: 2px solid var(--brand-200);
        }
        .flow-text strong { font-size: 13px; font-weight: 600; display: block; }
        .flow-text span { font-size: 11.5px; color: var(--text-muted); }

        /* ══════════════ PAGINATION ══════════════ */
        .pagination { display: flex; gap: 4px; list-style: none; padding: 16px 0 0; justify-content: center; flex-wrap: wrap; }
        .pagination a, .pagination span {
            padding: 6px 12px; border: 1.5px solid var(--border);
            border-radius: var(--radius-sm); font-size: 12.5px; font-weight: 600;
            text-decoration: none; color: var(--text-muted);
            transition: all .12s;
        }
        .pagination a:hover { border-color: var(--brand-400); color: var(--brand-600); background: var(--brand-50); }
        .pagination .active span { background: var(--brand-600); color: #fff; border-color: var(--brand-600); }

        /* ══════════════ EMPTY STATE ══════════════ */
        .empty-state {
            display: flex; flex-direction: column; align-items: center;
            padding: 48px 24px; text-align: center;
            color: var(--text-muted);
        }
        .empty-icon {
            width: 64px; height: 64px; border-radius: 50%;
            background: var(--surface-3); color: var(--text-ghost);
            display: flex; align-items: center; justify-content: center;
            font-size: 26px; margin-bottom: 16px;
        }
        .empty-state h3 { font-size: 15px; font-weight: 700; color: var(--text-2); margin-bottom: 6px; }
        .empty-state p  { font-size: 13px; max-width: 380px; line-height: 1.6; }

        /* ══════════════ DETAIL SECTION ══════════════ */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 24px; }
        .info-row { padding: 10px 0; border-bottom: 1px solid var(--border); }
        .info-row:last-child { border-bottom: none; }
        .info-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--text-ghost); margin-bottom: 3px; }
        .info-value { font-size: 13.5px; font-weight: 500; color: var(--text); }

        .text-block {
            background: var(--surface-2); border: 1px solid var(--border);
            border-radius: var(--radius-sm); padding: 14px 16px;
            font-size: 13px; line-height: 1.65; color: var(--text-2);
        }

        /* ══════════════ ACTION PANEL ══════════════ */
        .action-card {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 14px;
        }
        .action-card.approve { border-color: var(--success-border); background: var(--success-bg); }
        .action-card.reject  { border-color: var(--danger-border); background: var(--danger-bg); }
        .action-card.active  { border-color: var(--info-border); background: var(--info-bg); }
        .action-card-title {
            font-size: 13px; font-weight: 700; margin-bottom: 14px;
            display: flex; align-items: center; gap: 7px;
        }

        /* ══════════════ FILTER BAR ══════════════ */
        .filter-bar {
            display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;
            padding: 16px 22px; background: var(--surface-2);
            border-bottom: 1px solid var(--border);
        }
        .filter-bar .form-group { margin-bottom: 0; }
        .filter-bar .form-label { font-size: 11px; margin-bottom: 4px; }
        .filter-bar select, .filter-bar input { font-size: 12.5px; padding: 7px 10px; }

        /* ══════════════ AUDIT ACTION BADGE ══════════════ */
        .action-badge {
            padding: 3px 9px; border-radius: 999px;
            font-size: 10.5px; font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
            letter-spacing: .2px;
        }
        .action-login { background: #dcfce7; color: #166534; }
        .action-logout { background: var(--surface-3); color: var(--text-muted); }
        .action-failed { background: #fee2e2; color: #991b1b; }
        .action-download { background: #dbeafe; color: #1e40af; }
        .action-upload { background: #e0e7ff; color: #3730a3; }
        .action-approve { background: #dcfce7; color: #166534; }
        .action-reject { background: #fee2e2; color: #991b1b; }
        .action-default { background: var(--surface-3); color: var(--text-2); }

        /* ══════════════ RESPONSIVE ══════════════ */
        @media (max-width: 1024px) {
            .section-grid, .two-col, .detail-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 900px) {
            .sidebar { transform: translateX(-100%); transition: transform .25s; }
            .sidebar.open { transform: translateX(0); }
            .main-wrap { margin-left: 0; }
            .topbar { padding: 0 16px; }
            .main-content { padding: 16px; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon"><i class="fas fa-landmark"></i></div>
            <div class="sidebar-brand-text">
                <h1>Aplikasi Berbagi Data</h1>
                <p>Portal pertukaran data</p>
            </div>
        </div>

        <div class="sidebar-user">
            <div class="user-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
            <div class="user-info">
                <strong>{{ auth()->user()->name }}</strong>
                <span>{{ auth()->user()->instansi }}</span>
                @if(auth()->user()->isSuperAdmin())
                    <div class="role-pill"><i class="fas fa-crown" style="font-size:9px"></i> Super Admin</div>
                @else
                    <div class="role-pill admin"><i class="fas fa-building" style="font-size:9px"></i> Admin OPD</div>
                @endif
            </div>
        </div>

        <nav>
            @if(auth()->user()->isSuperAdmin())
                <div class="nav-label">Utama</div>
                <a href="{{ route('superadmin.dashboard') }}" class="nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a>

                <div class="nav-label">Dataset</div>
                <a href="{{ route('superadmin.files.index') }}" class="nav-link {{ request()->routeIs('superadmin.files.*') ? 'active' : '' }}">
                    <i class="fas fa-database"></i> Kelola Dataset
                </a>
                <a href="{{ route('superadmin.nda-templates.index') }}" class="nav-link {{ request()->routeIs('superadmin.nda-templates.*') ? 'active' : '' }}">
                    <i class="fas fa-file-contract"></i> Template Dokumen
                </a>

                <div class="nav-label">Permintaan</div>
                <a href="{{ route('superadmin.requests.index') }}" class="nav-link {{ ($approvalNotifications['data_requests'] ?? 0) > 0 ? 'has-notification' : '' }} {{ request()->routeIs('superadmin.requests.*') ? 'active' : '' }}">
                    <i class="fas fa-inbox"></i> Permintaan Akses
                    @if(($approvalNotifications['data_requests'] ?? 0) > 0)
                        <span class="nav-badge" title="{{ $approvalNotifications['data_requests'] }} menunggu approval">{{ $approvalNotifications['data_requests'] > 99 ? '99+' : $approvalNotifications['data_requests'] }}</span>
                    @endif
                </a>
                <a href="{{ route('superadmin.evaluations.index') }}" class="nav-link {{ request()->routeIs('superadmin.evaluations.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-check"></i> Evaluasi Pemanfaatan
                </a>
                <a href="{{ route('monitoring.requests.index') }}" class="nav-link {{ request()->routeIs('monitoring.requests.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-simple"></i> Monitoring Permohonan
                </a>

                <div class="nav-label">Manajemen</div>
                <a href="{{ route('superadmin.user-registrations.index') }}" class="nav-link {{ ($approvalNotifications['user_registrations'] ?? 0) > 0 ? 'has-notification' : '' }} {{ request()->routeIs('superadmin.user-registrations.*') ? 'active' : '' }}">
                    <i class="fas fa-user-check"></i> Permohonan User
                    @if(($approvalNotifications['user_registrations'] ?? 0) > 0)
                        <span class="nav-badge" title="{{ $approvalNotifications['user_registrations'] }} menunggu approval">{{ $approvalNotifications['user_registrations'] > 99 ? '99+' : $approvalNotifications['user_registrations'] }}</span>
                    @endif
                </a>
                <a href="{{ route('superadmin.users.index') }}" class="nav-link {{ request()->routeIs('superadmin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users-gear"></i> Pengguna OPD
                </a>
                <a href="{{ route('superadmin.download-pic.edit') }}" class="nav-link {{ request()->routeIs('superadmin.download-pic.*') ? 'active' : '' }}">
                    <i class="fas fa-address-card"></i> Kontak PIC
                </a>

                <div class="nav-label">Kepatuhan</div>
                <a href="{{ route('superadmin.audit.index') }}" class="nav-link {{ request()->routeIs('superadmin.audit.index') || request()->routeIs('superadmin.audit.export') ? 'active' : '' }}">
                    <i class="fas fa-shield-halved"></i> Log Aktivitas
                </a>
                <a href="{{ route('superadmin.audit.downloads') }}" class="nav-link {{ request()->routeIs('superadmin.audit.downloads*') ? 'active' : '' }}">
                    <i class="fas fa-arrow-down-to-line"></i> Log Unduhan
                </a>
            @else
                <div class="nav-label">Utama</div>
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a>

                <div class="nav-label">Data</div>
                <a href="{{ route('admin.requests.index') }}" class="nav-link {{ request()->routeIs('admin.requests.*') ? 'active' : '' }}">
                    <i class="fas fa-file-lines"></i> Permintaan Saya
                </a>
                <a href="{{ route('admin.requests.create') }}" class="nav-link {{ request()->routeIs('admin.requests.create') ? 'active' : '' }}">
                    <i class="fas fa-circle-plus"></i> Ajukan Permintaan
                </a>
                <a href="{{ route('admin.evaluations.index') }}" class="nav-link {{ request()->routeIs('admin.evaluations.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-check"></i> Evaluasi Pemanfaatan
                </a>
                @if(auth()->user()->canAccessRequestMonitoring())
                    <a href="{{ route('monitoring.requests.index') }}" class="nav-link {{ request()->routeIs('monitoring.requests.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-simple"></i> Monitoring Permohonan
                    </a>
                @endif
            @endif

            <div class="nav-label">Akun</div>
            <a href="{{ route('password.edit') }}" class="nav-link {{ request()->routeIs('password.*') ? 'active' : '' }}">
                <i class="fas fa-key"></i> Ubah / Reset Password
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-clock">{{ now()->format('d/m/Y H:i') }}</div>
            <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit()">
                <i class="fas fa-right-from-bracket"></i> Keluar dari Sistem
            </a>
        </div>
    </aside>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none">@csrf</form>

    <!-- Main -->
    <div class="main-wrap">
        <header class="topbar">
            <div class="topbar-left">
                <div>
                    <div class="page-eyebrow">
                        {{ auth()->user()->isSuperAdmin() ? 'Super Admin' : auth()->user()->instansi }}
                    </div>
                    <div class="page-title">@yield('page-title', 'Dashboard')</div>
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-badge">
                    <i class="fas fa-shield-halved"></i> Storage Privat
                </div>
                <div class="topbar-sep"></div>
                <div class="topbar-user">
                    <i class="fas fa-circle-user"></i>
                    {{ auth()->user()->name }}
                </div>
            </div>
        </header>

        <div class="main-content">
            @if(session('success'))
                <div class="alert alert-success"><i class="fas fa-circle-check"></i><div>{{ session('success') }}</div></div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning"><i class="fas fa-triangle-exclamation"></i><div>{{ session('warning') }}</div></div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger"><i class="fas fa-circle-xmark"></i><div>{{ session('error') }}</div></div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-triangle-exclamation"></i>
                    <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
                </div>
            @endif
            @yield('content')
        </div>
    </div>
    @stack('scripts')
</body>
</html>
