@extends('layouts.app')
@section('page-title', 'Dashboard')

@section('content')

<div class="pdp-notice">
    <i class="fas fa-shield-halved"></i>
    <div><strong>UU PDP No.27/2022 — Tanggung Jawab Pengendali Data:</strong> Sebagai Super Admin, Anda bertanggung jawab memastikan keamanan, keabsahan, dan kepatuhan seluruh pemrosesan data pribadi dalam sistem ini.</div>
</div>

<div class="stats-grid">
    <a href="{{ route('superadmin.files.index') }}" class="stat-card blue">
        <div class="stat-top">
            <div class="stat-icon blue"><i class="fas fa-database"></i></div>
            <span class="stat-trend"><i class="fas fa-arrow-up"></i> Aktif</span>
        </div>
        <div class="stat-num">{{ $stats['total_files'] }}</div>
        <div class="stat-label">Total Dataset</div>
    </a>
    <a href="{{ route('superadmin.requests.index', ['status' => 'pending']) }}" class="stat-card orange">
        <div class="stat-top">
            <div class="stat-icon orange"><i class="fas fa-hourglass-half"></i></div>
        </div>
        <div class="stat-num">{{ $stats['pending_requests'] }}</div>
        <div class="stat-label">Permintaan Menunggu</div>
    </a>
    <a href="{{ route('superadmin.users.index') }}" class="stat-card green">
        <div class="stat-top">
            <div class="stat-icon green"><i class="fas fa-building-user"></i></div>
        </div>
        <div class="stat-num">{{ $stats['total_admins'] }}</div>
        <div class="stat-label">Admin OPD Terdaftar</div>
    </a>
    <a href="{{ route('superadmin.audit.downloads') }}" class="stat-card red">
        <div class="stat-top">
            <div class="stat-icon red"><i class="fas fa-arrow-down-to-line"></i></div>
        </div>
        <div class="stat-num">{{ $stats['downloads_today'] }}</div>
        <div class="stat-label">Unduhan Hari Ini</div>
    </a>
</div>

<div class="two-col">
    <!-- Permintaan terbaru -->
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-inbox"></i> Permintaan Data Terbaru</span>
            <a href="{{ route('superadmin.requests.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline">
                Lihat Semua <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div style="padding: 4px 22px 8px;">
            @forelse($recentRequests as $req)
            <div class="quick-item">
                <div class="quick-item-info">
                    <strong>{{ $req->user->name }}</strong>
                    <span><i class="fas fa-building" style="font-size:10px;"></i> {{ $req->user->instansi }}</span>
                    <span style="font-size:11.5px; color:#94a3b8; margin-top:1px;">
                        <i class="fas fa-table" style="font-size:10px;"></i> {{ $req->dataFile->judul }}
                        &bull; {{ $req->created_at->diffForHumans() }}
                    </span>
                </div>
                <div style="display:flex; gap:8px; align-items:center; flex-shrink:0;">
                    <span class="badge badge-{{ $req->status }}">{{ $req->status_label }}</span>
                    <a href="{{ route('superadmin.requests.show', $req) }}" class="btn btn-xs btn-outline">
                        <i class="fas fa-eye"></i>
                    </a>
                </div>
            </div>
            @empty
            <div class="empty-state" style="padding: 36px 0;">
                <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                <h3>Belum ada permintaan</h3>
                <p>Permintaan akses data dari OPD akan muncul di sini.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Aktivitas terkini -->
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-clock-rotate-left"></i> Aktivitas Terkini</span>
            <a href="{{ route('superadmin.audit.index') }}" class="btn btn-sm btn-outline">
                Audit Log <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div style="padding: 4px 22px 8px;">
            @forelse($recentActivity as $log)
            <div class="quick-item">
                <div style="display:flex; align-items:flex-start; gap:12px;">
                    @php
                        $icons = [
                            'login'          => ['fas fa-right-to-bracket','#059669'],
                            'file_upload'    => ['fas fa-upload',           '#7c3aed'],
                            'file_download'  => ['fas fa-download',         '#2563eb'],
                            'request_approve'=> ['fas fa-circle-check',     '#059669'],
                            'request_reject' => ['fas fa-circle-xmark',     '#dc2626'],
                            'logout'         => ['fas fa-right-from-bracket','#94a3b8'],
                        ];
                        $ico = $icons[$log->action] ?? ['fas fa-circle-dot', '#94a3b8'];
                    @endphp
                    <div style="width:32px;height:32px;border-radius:8px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;font-size:13px;color:{{ $ico[1] }};flex-shrink:0;">
                        <i class="{{ $ico[0] }}"></i>
                    </div>
                    <div class="quick-item-info">
                        <strong>{{ $log->user->name ?? 'Sistem' }}</strong>
                        <span>{{ $log->action_label }} &bull; {{ $log->actor_username }}</span>
                        <span>{{ $log->actor_institution }}</span>
                        <span style="color:#94a3b8;">{{ $log->occurred_at->format('d/m H:i') }} &bull; {{ $log->ip_address }}</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="empty-state" style="padding:36px 0;">
                <div class="empty-icon"><i class="fas fa-clock"></i></div>
                <h3>Belum ada aktivitas</h3>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
