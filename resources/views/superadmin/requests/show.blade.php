@extends('layouts.app')
@section('page-title', 'Review Permintaan #' . $dataRequest->id)

@section('content')
<div style="margin-bottom:20px;">
    <a href="{{ route('superadmin.requests.index') }}" class="btn btn-ghost btn-sm">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar
    </a>
</div>

<div class="detail-grid">
    <!-- Left: Detail info -->
    <div>
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-file-lines"></i> Detail Permintaan #{{ $dataRequest->id }}</span>
                <span class="badge badge-{{ $dataRequest->status }}">{{ $dataRequest->status_label }}</span>
            </div>
            <div class="card-body">
                <div class="info-grid" style="margin-bottom:18px;">
                    <div class="info-row">
                        <div class="info-label">Pemohon</div>
                        <div class="info-value">{{ $dataRequest->user->name }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Instansi / OPD</div>
                        <div class="info-value">{{ $dataRequest->user->instansi }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Dataset Diminta</div>
                        <div class="info-value" style="font-weight:700;">{{ $dataRequest->dataFile->judul }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Dasar Hukum</div>
                        <div class="info-value">{{ $dataRequest->dasar_hukum }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Tanggal Diajukan</div>
                        <div class="info-value">{{ $dataRequest->created_at->format('d F Y, H:i') }}</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-comment-dots" style="color:var(--text-ghost)"></i> Alasan Permintaan</label>
                    <div class="text-block">{{ $dataRequest->alasan_permintaan }}</div>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label"><i class="fas fa-bullseye" style="color:var(--text-ghost)"></i> Tujuan Penggunaan</label>
                    <div class="text-block">{{ $dataRequest->tujuan_penggunaan }}</div>
                </div>

                @if($dataRequest->nda_path)
                <div style="margin-top:20px; padding:14px 16px; background:var(--success-bg); border:1.5px solid var(--success-border); border-radius:var(--radius-sm); display:flex; justify-content:space-between; align-items:center; gap:12px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <i class="fas fa-file-pdf" style="color:#dc2626;font-size:20px;"></i>
                        <div>
                            <div style="font-size:13px;font-weight:700;color:var(--text);">NDA Terlampir</div>
                            <div style="font-size:11.5px;color:var(--text-muted);">{{ $dataRequest->nda_filename }}</div>
                        </div>
                    </div>
                    <div style="display:flex;gap:8px;flex-shrink:0;">
                        <a href="{{ route('superadmin.requests.nda', $dataRequest) }}" target="_blank" class="btn btn-sm btn-outline">
                            <i class="fas fa-eye"></i> Lihat
                        </a>
                        <a href="{{ route('superadmin.requests.nda', ['dataRequest' => $dataRequest, 'mode' => 'download']) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-download"></i> Unduh
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Download log -->
        @if($dataRequest->downloadLogs->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-clock-rotate-left"></i> Riwayat Unduhan</span>
                <span style="font-size:12px;color:var(--text-muted);">{{ $dataRequest->downloadLogs->count() }} unduhan</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Pengguna</th>
                            <th>IP Address</th>
                            <th>Captcha</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($dataRequest->downloadLogs as $log)
                    <tr>
                        <td>
                            <div style="font-size:12.5px;font-weight:600;">{{ $log->downloaded_at->format('d/m/Y') }}</div>
                            <div style="font-size:11px;color:var(--text-ghost);font-family:'JetBrains Mono',monospace;">{{ $log->downloaded_at->format('H:i:s') }}</div>
                        </td>
                        <td>
                            <div style="font-weight:600;font-size:13px;">{{ $log->user->name ?? '—' }}</div>
                            <div style="font-size:11px;color:var(--text-ghost);">{{ $log->user->instansi ?? '' }}</div>
                        </td>
                        <td style="font-family:'JetBrains Mono',monospace;font-size:12px;">{{ $log->ip_address }}</td>
                        <td style="text-align:center;">
                            @if($log->captcha_passed)
                                <i class="fas fa-circle-check" style="color:var(--success);"></i>
                            @else
                                <i class="fas fa-circle-xmark" style="color:var(--danger);"></i>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $log->status === 'success' ? 'badge-approved' : 'badge-rejected' }}">{{ $log->status }}</span>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <!-- Right: Actions -->
    <div>
        @if($dataRequest->isPending())
        <div class="action-card approve" style="margin-bottom:14px;">
            <div class="action-card-title" style="color:var(--success);">
                <i class="fas fa-circle-check"></i> Setujui Permintaan
            </div>
            <form action="{{ route('superadmin.requests.approve', $dataRequest) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Catatan (opsional)</label>
                    <textarea name="catatan" class="form-control" rows="3" placeholder="Catatan atau syarat khusus untuk pemohon..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Limit Download <span class="required">*</span></label>
                    <input type="number" name="max_downloads" class="form-control" value="{{ old('max_downloads', $dataRequest->max_downloads ?? 3) }}" min="1" max="999" required>
                    <div class="form-text">Jumlah maksimal unduhan untuk periode yang dipilih.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Reset Kuota <span class="required">*</span></label>
                    <select name="quota_period" class="form-control" required>
                        <option value="daily" {{ old('quota_period', $dataRequest->quota_period ?? 'weekly') === 'daily' ? 'selected' : '' }}>Harian</option>
                        <option value="weekly" {{ old('quota_period', $dataRequest->quota_period ?? 'weekly') === 'weekly' ? 'selected' : '' }}>Mingguan</option>
                        <option value="monthly" {{ old('quota_period', $dataRequest->quota_period ?? 'weekly') === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                        <option value="lifetime" {{ old('quota_period', $dataRequest->quota_period ?? 'weekly') === 'lifetime' ? 'selected' : '' }}>Selamanya</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success" style="width:100%;justify-content:center;">
                    <i class="fas fa-circle-check"></i> Setujui Akses
                </button>
            </form>
        </div>

        <div class="action-card reject">
            <div class="action-card-title" style="color:var(--danger);">
                <i class="fas fa-circle-xmark"></i> Tolak Permintaan
            </div>
            <form action="{{ route('superadmin.requests.reject', $dataRequest) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Alasan Penolakan <span class="required">*</span></label>
                    <textarea name="catatan" class="form-control" rows="3" required placeholder="Jelaskan alasan penolakan kepada pemohon..."></textarea>
                </div>
                <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center;"
                    onclick="return confirm('Yakin menolak permintaan ini?')">
                    <i class="fas fa-circle-xmark"></i> Tolak Permintaan
                </button>
            </form>
        </div>
        @endif

        @if($dataRequest->isApproved())
        <div class="action-card active" style="margin-bottom:14px;">
            <div class="action-card-title" style="color:var(--info);">
                <i class="fas fa-circle-info"></i> Status Akses
            </div>
            <div style="font-size:13px;">
                <div style="font-weight:700;color:var(--success);margin-bottom:10px;">
                    <i class="fas fa-circle-check"></i> Akses Disetujui
                </div>
                <div class="info-row">
                    <div class="info-label">Disetujui oleh</div>
                    <div class="info-value">{{ $dataRequest->reviewer->name ?? '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Pada tanggal</div>
                    <div class="info-value">{{ $dataRequest->reviewed_at?->format('d/m/Y H:i') }}</div>
                </div>
                <div class="info-row" style="margin-bottom:0;">
                    <div class="info-label">Sisa unduhan {{ $dataRequest->quotaWindowLabel() }}</div>
                    <div class="info-value" style="font-weight:700;color:var(--brand-600);">
                        {{ $dataRequest->remainingDownloads() }}x dari {{ $dataRequest->max_downloads }}x
                    </div>
                </div>
            </div>
        </div>

        <div class="action-card active" style="margin-bottom:14px;">
            <div class="action-card-title" style="color:var(--brand-600);">
                <i class="fas fa-sliders"></i> Atur Kuota Download
            </div>
            <form action="{{ route('superadmin.requests.quota', $dataRequest) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Limit Download</label>
                    <input type="number" name="max_downloads" class="form-control" value="{{ old('max_downloads', $dataRequest->max_downloads) }}" min="1" max="999" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Reset Kuota</label>
                    <select name="quota_period" class="form-control" required>
                        <option value="daily" {{ $dataRequest->quotaPeriod() === 'daily' ? 'selected' : '' }}>Harian</option>
                        <option value="weekly" {{ $dataRequest->quotaPeriod() === 'weekly' ? 'selected' : '' }}>Mingguan</option>
                        <option value="monthly" {{ $dataRequest->quotaPeriod() === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                        <option value="lifetime" {{ $dataRequest->quotaPeriod() === 'lifetime' ? 'selected' : '' }}>Selamanya</option>
                    </select>
                    <div class="form-text">Saat ini: {{ $dataRequest->max_downloads }}x {{ $dataRequest->quotaPeriodLabel() }}.</div>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                    <i class="fas fa-save"></i> Simpan Kuota
                </button>
            </form>
            <form action="{{ route('superadmin.requests.quota.reset', $dataRequest) }}" method="POST" style="margin-top:10px;">
                @csrf
                <button type="submit" class="btn btn-outline" style="width:100%;justify-content:center;" onclick="return confirm('Reset kuota download untuk permintaan ini?')">
                    <i class="fas fa-rotate"></i> Reset Kuota Sekarang
                </button>
            </form>
        </div>

        <div class="action-card reject">
            <div class="action-card-title" style="color:var(--warning);">
                <i class="fas fa-ban"></i> Cabut Persetujuan
            </div>
            <form action="{{ route('superadmin.requests.revoke', $dataRequest) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Alasan Pencabutan <span class="required">*</span></label>
                    <textarea name="catatan" class="form-control" rows="2" required placeholder="Alasan pencabutan akses..."></textarea>
                </div>
                <button type="submit" class="btn btn-warning" style="width:100%;justify-content:center;"
                    onclick="return confirm('Yakin mencabut persetujuan akses ini?')">
                    <i class="fas fa-ban"></i> Cabut Akses
                </button>
            </form>
        </div>
        @endif

        @if($dataRequest->catatan_reviewer)
        <div class="action-card" style="margin-top:14px;">
            <div class="action-card-title">
                <i class="fas fa-comment-dots" style="color:var(--text-muted);"></i> Catatan Reviewer
            </div>
            <div class="text-block">{{ $dataRequest->catatan_reviewer }}</div>
        </div>
        @endif
    </div>
</div>
@endsection
