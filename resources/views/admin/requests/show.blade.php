@extends('layouts.app')
@section('page-title', 'Detail Permintaan #' . $dataRequest->id)

@section('content')
<div style="margin-bottom:20px;">
    <a href="{{ route('admin.requests.index') }}" class="btn btn-ghost btn-sm">
        <i class="fas fa-arrow-left"></i> Kembali ke Permintaan Saya
    </a>
</div>

<div style="max-width:860px;">
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-file-lines"></i> Permintaan #{{ $dataRequest->id }}</span>
            <div style="display:flex; gap:8px; align-items:center;">
                <span class="badge badge-{{ $dataRequest->status }}">{{ $dataRequest->status_label }}</span>
            </div>
        </div>
        <div class="card-body">
            <div class="info-grid" style="margin-bottom:20px;">
                <div class="info-row">
                    <div class="info-label">Dataset</div>
                    <div class="info-value" style="font-weight:700;">{{ $dataRequest->dataFile->judul ?? '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Kategori &amp; Periode</div>
                    <div class="info-value">{{ $dataRequest->dataFile->kategori_label ?? '' }} · {{ $dataRequest->dataFile->tahun_data ?? '' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Dasar Hukum</div>
                    <div class="info-value">{{ $dataRequest->dasar_hukum }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tanggal Pengajuan</div>
                    <div class="info-value">{{ $dataRequest->created_at->format('d F Y, H:i') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">NDA Terlampir</div>
                    <div class="info-value">
                        <i class="fas fa-file-pdf" style="color:#dc2626;"></i> {{ $dataRequest->nda_filename }}
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Alasan Permintaan</label>
                <div class="text-block">{{ $dataRequest->alasan_permintaan }}</div>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Tujuan Penggunaan</label>
                <div class="text-block">{{ $dataRequest->tujuan_penggunaan }}</div>
            </div>

            @if($dataRequest->catatan_reviewer)
            <div style="margin-top:18px; padding:14px 16px; background:{{ $dataRequest->isApproved() ? 'var(--success-bg)' : 'var(--danger-bg)' }}; border:1.5px solid {{ $dataRequest->isApproved() ? 'var(--success-border)' : 'var(--danger-border)' }}; border-radius:var(--radius-sm); font-size:13px;">
                <div style="font-weight:700; margin-bottom:5px; color:{{ $dataRequest->isApproved() ? 'var(--success)' : 'var(--danger)' }};">
                    <i class="fas fa-comment-dots"></i> Catatan Reviewer — {{ $dataRequest->reviewer->name ?? '—' }} ({{ $dataRequest->reviewed_at?->format('d/m/Y H:i') }})
                </div>
                {{ $dataRequest->catatan_reviewer }}
            </div>
            @endif
        </div>
    </div>

    <!-- Status Action -->
    @if($dataRequest->isApproved())
        @if($dataRequest->canDownload())
        <div class="card" style="border:1.5px solid var(--success-border); background:var(--success-bg);">
            <div class="card-body" style="text-align:center; padding:28px;">
                <div style="width:56px; height:56px; border-radius:50%; background:var(--success); display:flex; align-items:center; justify-content:center; font-size:22px; color:#fff; margin:0 auto 16px;">
                    <i class="fas fa-circle-check"></i>
                </div>
                <div style="font-size:16px; font-weight:800; color:var(--success); margin-bottom:8px;">Permintaan Disetujui — Siap Diunduh</div>
                <div style="font-size:13px; color:#065f46; margin-bottom:20px;">
                    Sisa unduhan {{ $dataRequest->quotaWindowLabel() }}: <strong>{{ $dataRequest->remainingDownloads() }}x</strong> &nbsp;|&nbsp;
                    Kuota berlaku sampai: <strong>{{ $dataRequest->quotaPeriod() === 'lifetime' ? 'Tidak reset otomatis' : ($dataRequest->token_expires_at?->format('d/m/Y H:i') ?? 'N/A') }}</strong>
                </div>
                <a href="{{ route('admin.download.show', $dataRequest) }}" class="btn btn-success" style="font-size:14px; padding:11px 24px;">
                    <i class="fas fa-download"></i> Unduh File (Perlu Verifikasi Captcha)
                </a>
            </div>
        </div>
        @else
        <div class="alert alert-warning">
            <i class="fas fa-triangle-exclamation"></i>
            <div>Batas unduhan {{ $dataRequest->quotaPeriodLabel() }} ({{ $dataRequest->max_downloads }}x) telah tercapai. Hubungi Super Admin jika kuota perlu direset.</div>
        </div>
        @endif
    @elseif($dataRequest->isPending())
    <div class="alert alert-info">
        <i class="fas fa-hourglass-half"></i>
        <div>Permintaan Anda sedang ditinjau oleh Super Admin. Anda akan diberitahu setelah ada keputusan, biasanya 1-3 hari kerja.</div>
    </div>
    @elseif($dataRequest->isRejected())
    <div class="alert alert-danger">
        <i class="fas fa-circle-xmark"></i>
        <div>Permintaan ditolak. Periksa catatan reviewer di atas, lalu kirim revisi jika dokumen atau penjelasan perlu diperbaiki.</div>
    </div>
    <div style="margin-top:12px;">
        <a href="{{ route('admin.requests.edit', $dataRequest) }}" class="btn btn-primary">
            <i class="fas fa-file-pen"></i> Revisi Permintaan
        </a>
    </div>
    @endif

    <!-- Download History -->
    @if($dataRequest->downloadLogs->isNotEmpty())
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-clock-rotate-left"></i> Riwayat Unduhan Saya</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Waktu</th><th>IP Address</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @foreach($dataRequest->downloadLogs as $log)
                    <tr>
                        <td>
                            <div style="font-size:12.5px; font-weight:600;">{{ $log->downloaded_at->format('d/m/Y') }}</div>
                            <div style="font-size:11px; color:var(--text-ghost); font-family:'JetBrains Mono',monospace;">{{ $log->downloaded_at->format('H:i:s') }}</div>
                        </td>
                        <td style="font-family:'JetBrains Mono',monospace; font-size:12px; color:var(--text-muted);">{{ $log->ip_address }}</td>
                        <td><span class="badge {{ $log->status === 'success' ? 'badge-approved' : 'badge-rejected' }}">{{ $log->status }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
