@extends('layouts.app')
@section('page-title', 'Monitoring Permohonan Data')

@section('content')
@php
    $statusLabels = [
        'pending' => 'Menunggu Persetujuan',
        'returned' => 'Dikembalikan',
        'approved' => 'Menunggu Upload BAST',
        'bast_pending' => 'BAST Menunggu Verifikasi',
        'bast_approved' => 'Disetujui',
        'bast_rejected' => 'BAST Ditolak',
        'rejected' => 'Ditolak',
        'revoked' => 'Dicabut',
    ];

    $statusIcons = [
        'pending' => 'fa-clock',
        'returned' => 'fa-rotate-left',
        'approved' => 'fa-file-arrow-up',
        'bast_pending' => 'fa-file-circle-question',
        'bast_approved' => 'fa-circle-check',
        'bast_rejected' => 'fa-file-circle-xmark',
        'rejected' => 'fa-circle-xmark',
        'revoked' => 'fa-ban',
    ];

    $statusCardColors = [
        'pending' => 'orange',
        'returned' => 'orange',
        'approved' => 'blue',
        'bast_pending' => 'orange',
        'bast_approved' => 'green',
        'bast_rejected' => 'red',
        'rejected' => 'red',
        'revoked' => 'blue',
    ];
@endphp

<div class="stats-grid">
    <a href="{{ route('monitoring.requests.index') }}" class="stat-card blue">
        <div class="stat-top">
            <div>
                <div class="stat-num">{{ $statusCounts->sum() }}</div>
                <div class="stat-label">Semua Permohonan</div>
            </div>
            <div class="stat-icon blue"><i class="fas fa-layer-group"></i></div>
        </div>
    </a>

    @foreach($statuses as $status)
        @php
            $color = $statusCardColors[$status] ?? 'blue';
            $isActive = request('status') === $status;
        @endphp
        <a href="{{ route('monitoring.requests.index', ['status' => $status, 'q' => request('q')]) }}"
           class="stat-card {{ $color }}"
           style="{{ $isActive ? 'outline:2px solid var(--brand-300);' : '' }}">
            <div class="stat-top">
                <div>
                    <div class="stat-num">{{ $statusCounts[$status] ?? 0 }}</div>
                    <div class="stat-label">{{ $statusLabels[$status] ?? $status }}</div>
                </div>
                <div class="stat-icon {{ $color }}"><i class="fas {{ $statusIcons[$status] ?? 'fa-circle' }}"></i></div>
            </div>
        </a>
    @endforeach
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-chart-simple"></i> Monitoring Permohonan Data</span>
        <a href="{{ route('monitoring.requests.index') }}" class="btn btn-sm btn-ghost">
            <i class="fas fa-rotate"></i> Reset
        </a>
    </div>

    <form method="GET" class="filter-bar">
        <div class="form-group" style="min-width:260px;flex:1;">
            <label class="form-label" for="q">Cari Pemohon / OPD / Dataset</label>
            <input id="q" type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Ketik kata kunci">
        </div>
        <div class="form-group" style="min-width:220px;">
            <label class="form-label" for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="">Semua Status</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                        {{ $statusLabels[$status] ?? $status }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-magnifying-glass"></i> Terapkan
        </button>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Status</th>
                    <th>Pemohon &amp; OPD</th>
                    <th>Dataset</th>
                    <th>Dokumen</th>
                    <th>Verifikasi</th>
                    <th>Unduhan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                    <tr>
                        <td style="color:var(--text-ghost);font-family:'JetBrains Mono',monospace;font-size:12px;">{{ $req->id }}</td>
                        <td>
                            <span class="badge badge-{{ $req->status }}">{{ $req->status_label }}</span>
                            <div style="font-size:11px;color:var(--text-ghost);margin-top:5px;">
                                {{ optional($req->created_at)->format('d/m/Y H:i') }}
                            </div>
                        </td>
                        <td>
                            <div style="font-weight:700;font-size:13px;">{{ $req->user->name ?? '-' }}</div>
                            <div style="font-size:11.5px;color:var(--text-muted);margin-top:2px;">
                                <i class="fas fa-building" style="font-size:10px;"></i> {{ $req->user->instansi ?? '-' }}
                            </div>
                        </td>
                        <td>
                            <div style="font-size:13px;font-weight:600;">{{ $req->dataFile->judul ?? '-' }}</div>
                            <div style="font-size:11px;color:var(--text-ghost);margin-top:2px;">
                                {{ $req->dataFile->kategori_label ?? $req->dataFile->kategori ?? '' }}
                                {{ $req->dataFile?->tahun_data ? ' - '.$req->dataFile->tahun_data : '' }}
                            </div>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                @if($req->nda_path)
                                    <a href="{{ route('monitoring.requests.document', ['dataRequest' => $req, 'type' => 'permohonan']) }}" target="_blank" class="btn btn-xs btn-outline">
                                        <i class="fas fa-file-pdf"></i> Permohonan
                                    </a>
                                @else
                                    <span class="badge badge-revoked">Permohonan belum ada</span>
                                @endif

                                @if($req->bast_path)
                                    <a href="{{ route('monitoring.requests.document', ['dataRequest' => $req, 'type' => 'bast']) }}" target="_blank" class="btn btn-xs btn-outline">
                                        <i class="fas fa-file-signature"></i> BAST
                                    </a>
                                @else
                                    <span class="badge badge-revoked">BAST belum ada</span>
                                @endif

                                @if($req->utilizationEvaluation)
                                    <a href="{{ route('monitoring.requests.document', ['dataRequest' => $req, 'type' => 'evaluasi']) }}" target="_blank" class="btn btn-xs btn-outline">
                                        <i class="fas fa-clipboard-check"></i> Evaluasi
                                    </a>
                                @else
                                    <span class="badge badge-revoked">Evaluasi belum ada</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($req->reviewer)
                                <div style="font-size:12.5px;font-weight:600;">Permohonan: {{ $req->reviewer->name }}</div>
                                <div style="font-size:11px;color:var(--text-ghost);">{{ optional($req->reviewed_at)->format('d/m/Y H:i') }}</div>
                            @else
                                <div style="font-size:12px;color:var(--text-ghost);">Permohonan belum diverifikasi</div>
                            @endif

                            @if($req->bastReviewer)
                                <div style="font-size:12.5px;font-weight:600;margin-top:7px;">BAST: {{ $req->bastReviewer->name }}</div>
                                <div style="font-size:11px;color:var(--text-ghost);">{{ optional($req->bast_reviewed_at)->format('d/m/Y H:i') }}</div>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <div style="font-size:18px;font-weight:800;color:var(--brand-600);">{{ $req->successful_downloads_count }}</div>
                            <div style="font-size:11px;color:var(--text-ghost);">berhasil</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fas fa-chart-simple"></i></div>
                                <h3>Belum ada permohonan sesuai filter</h3>
                                <p>Ubah kata kunci atau status untuk melihat data permohonan lainnya.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($requests->hasPages())
        <div style="padding:0 22px 18px;">{{ $requests->links() }}</div>
    @endif
</div>
@endsection
