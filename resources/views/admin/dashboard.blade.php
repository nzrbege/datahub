@extends('layouts.app')
@section('page-title', 'Dashboard')

@section('content')
@php
    $user = auth()->user();
    $accessibleFiles = $user->accessibleFiles()
        ->where('data_files.is_active', true)
        ->latest('data_files.created_at')
        ->get();

    $recentRequests = $user->dataRequests()
        ->with('dataFile')->latest()->limit(5)->get();
    $recentRequests->each->ensureDownloadToken();

    $activeRequests = $user->dataRequests()
        ->whereIn('status', ['pending', 'approved'])
        ->get()->keyBy('data_file_id');

    $totalRequests   = $user->dataRequests()->count();
    $pendingRequests = $user->dataRequests()->where('status', 'pending')->count();
    $approvedRequests= $user->dataRequests()->where('status', 'approved')->count();
    $downloads       = \App\Models\DownloadLog::where('user_id', $user->id)->where('status', 'success')->count();
@endphp

<div class="pdp-notice">
    <i class="fas fa-circle-info"></i>
    <div><strong>UU PDP No.27/2022 — Penerima Data:</strong> Akses dataset hanya digunakan sesuai tujuan yang disetujui. Seluruh unduhan tercatat dan dapat diperiksa sewaktu-waktu.</div>
</div>

<div class="stats-grid">
    <a href="{{ route('admin.requests.create') }}" class="stat-card blue">
        <div class="stat-top"><div class="stat-icon blue"><i class="fas fa-database"></i></div></div>
        <div class="stat-num">{{ $accessibleFiles->count() }}</div>
        <div class="stat-label">Dataset Tersedia</div>
    </a>
    <a href="{{ route('admin.requests.index', ['status' => 'pending']) }}" class="stat-card orange">
        <div class="stat-top"><div class="stat-icon orange"><i class="fas fa-hourglass-half"></i></div></div>
        <div class="stat-num">{{ $pendingRequests }}</div>
        <div class="stat-label">Menunggu Persetujuan</div>
    </a>
    <a href="{{ route('admin.requests.index', ['status' => 'approved']) }}" class="stat-card green">
        <div class="stat-top"><div class="stat-icon green"><i class="fas fa-circle-check"></i></div></div>
        <div class="stat-num">{{ $approvedRequests }}</div>
        <div class="stat-label">Akses Disetujui</div>
    </a>
    <a href="{{ route('admin.requests.index') }}" class="stat-card red">
        <div class="stat-top"><div class="stat-icon red"><i class="fas fa-arrow-down-to-line"></i></div></div>
        <div class="stat-num">{{ $downloads }}</div>
        <div class="stat-label">File Diunduh</div>
    </a>
</div>

<div class="section-grid">
    <!-- Dataset grid -->
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-database"></i> Dataset yang Dapat Diakses</span>
            <a href="{{ route('admin.requests.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-circle-plus"></i> Ajukan Permintaan
            </a>
        </div>
        <div style="padding: 18px 22px;">
            @if($accessibleFiles->isNotEmpty())
                <div class="dataset-grid">
                    @foreach($accessibleFiles as $file)
                        @php $requestForFile = $activeRequests->get($file->id); @endphp
                        <div class="dataset-card">
                            <div class="dataset-card__header">
                                <div class="dataset-card__icon"><i class="fas fa-table"></i></div>
                                <span class="badge badge-cat">{{ $file->kategori_label }}</span>
                            </div>
                            <div>
                                <div class="dataset-card__title">{{ $file->judul }}</div>
                                <div class="dataset-card__sub">{{ $file->original_filename }}</div>
                            </div>
                            <div class="dataset-meta">
                                <span class="dataset-meta-chip"><i class="fas fa-calendar" style="font-size:10px"></i> {{ $file->tahun_data ?? '—' }}</span>
                                <span class="dataset-meta-chip"><i class="fas fa-weight-hanging" style="font-size:10px"></i> {{ $file->file_size_human }}</span>
                                <span class="dataset-meta-chip"><i class="fas fa-lock" style="font-size:10px; color:var(--success)"></i> Terenkripsi</span>
                            </div>
                            <div class="dataset-desc">
                                {{ \Illuminate\Support\Str::limit($file->deskripsi ?: 'Dataset tersedia untuk diajukan melalui permintaan resmi dengan melampirkan NDA.', 110) }}
                            </div>
                            <div class="dataset-card__actions">
                                @if($requestForFile)
                                    <span class="badge badge-{{ $requestForFile->status }}">{{ $requestForFile->status_label }}</span>
                                    @if($requestForFile->canDownload())
                                        <a href="{{ route('admin.download.show', $requestForFile) }}" class="btn btn-xs btn-success">
                                            <i class="fas fa-download"></i> Unduh
                                        </a>
                                    @else
                                        <a href="{{ route('admin.requests.show', $requestForFile) }}" class="btn btn-xs btn-ghost">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    @endif
                                @else
                                    <span class="badge badge-active">Metadata Tersedia</span>
                                    <a href="{{ route('admin.requests.create') }}" class="btn btn-xs btn-primary">
                                        <i class="fas fa-file-signature"></i> Minta Akses
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-folder-open"></i></div>
                    <h3>Belum ada dataset yang diberikan akses</h3>
                    <p>Super Admin perlu mengizinkan akses dataset untuk instansi Anda terlebih dahulu sebelum dapat mengajukan permintaan dan melampirkan NDA.</p>
                    <div style="margin-top:20px;">
                        <a href="{{ route('admin.requests.create') }}" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Cek Form Permintaan
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Right sidebar -->
    <div>
        <!-- Permintaan terbaru -->
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-file-lines"></i> Permintaan Terbaru</span>
                <a href="{{ route('admin.requests.index') }}" class="btn btn-sm btn-outline">Semua</a>
            </div>
            <div style="padding: 0 22px 8px;">
                <div class="quick-list">
                    @forelse($recentRequests as $req)
                        <div class="quick-item">
                            <div class="quick-item-info">
                                <strong>{{ $req->dataFile->judul ?? 'Dataset tidak tersedia' }}</strong>
                                <span>{{ $req->created_at->diffForHumans() }}</span>
                            </div>
                            <div style="display:flex; gap:7px; align-items:center; flex-shrink:0;">
                                <span class="badge badge-{{ $req->status }}">{{ $req->status_label }}</span>
                                @if($req->canDownload())
                                    <a href="{{ route('admin.download.show', $req) }}" class="btn btn-xs btn-success">
                                        <i class="fas fa-download"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="empty-state" style="padding:28px 0;">
                            <div class="empty-icon"><i class="fas fa-file-circle-plus"></i></div>
                            <p style="font-size:13px;">Belum ada permintaan data.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Alur akses -->
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-route"></i> Alur Akses Data</span>
            </div>
            <div style="padding: 18px 22px 20px;">
                <div class="flow-steps">
                    <div class="flow-step">
                        <div class="flow-num">1</div>
                        <div class="flow-text">
                            <strong>Izin Super Admin</strong>
                            <span>Dataset dibuka aksesnya oleh Super Admin untuk instansi Anda</span>
                        </div>
                    </div>
                    <div class="flow-step">
                        <div class="flow-num">2</div>
                        <div class="flow-text">
                            <strong>Ajukan &amp; Lampirkan NDA</strong>
                            <span>Isi formulir permintaan resmi dan unggah dokumen NDA</span>
                        </div>
                    </div>
                    <div class="flow-step">
                        <div class="flow-num">3</div>
                        <div class="flow-text">
                            <strong>Review &amp; Persetujuan</strong>
                            <span>Super Admin meninjau dan memutuskan permintaan Anda</span>
                        </div>
                    </div>
                    <div class="flow-step">
                        <div class="flow-num">4</div>
                        <div class="flow-text">
                            <strong>Unduh dengan Verifikasi</strong>
                            <span>Selesaikan verifikasi captcha untuk mulai mengunduh</span>
                        </div>
                    </div>
                </div>
                <div style="margin-top: 20px;">
                    <a href="{{ route('admin.requests.create') }}" class="btn btn-primary" style="width:100%; justify-content:center;">
                        <i class="fas fa-circle-plus"></i> Ajukan Permintaan Baru
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
