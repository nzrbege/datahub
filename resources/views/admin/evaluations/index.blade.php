@extends('layouts.app')
@section('page-title', 'Evaluasi Pemanfaatan')

@push('styles')
<style>
    .evaluation-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .evaluation-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .evaluation-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        padding: 18px 22px;
        border-bottom: 1px solid var(--border);
        background: var(--surface-2);
    }

    .evaluation-title {
        font-size: 15px;
        font-weight: 800;
        color: var(--text);
        line-height: 1.35;
    }

    .evaluation-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 8px;
    }

    .evaluation-meta span {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 8px;
        border: 1px solid var(--border);
        border-radius: var(--radius-xs);
        background: var(--surface);
        color: var(--text-muted);
        font-size: 11.5px;
        font-weight: 600;
    }

    .evaluation-body {
        display: grid;
        grid-template-columns: minmax(220px, .9fr) minmax(240px, 1fr) minmax(300px, 1.25fr);
        gap: 18px;
        padding: 20px 22px 22px;
        align-items: start;
    }

    .evaluation-section {
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 14px;
        background: var(--surface);
        min-height: 100%;
    }

    .evaluation-section-title {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 12px;
        font-weight: 800;
        color: var(--text);
        text-transform: uppercase;
        letter-spacing: .4px;
        margin-bottom: 12px;
    }

    .evaluation-section-title i {
        color: var(--brand-500);
    }

    .evaluation-file {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        background: var(--surface-2);
        margin-bottom: 12px;
    }

    .evaluation-file-icon {
        width: 34px;
        height: 34px;
        border-radius: var(--radius-xs);
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fee2e2;
        color: #dc2626;
        flex-shrink: 0;
    }

    .evaluation-file-name {
        font-size: 12.5px;
        font-weight: 700;
        color: var(--text);
        word-break: break-word;
    }

    .evaluation-file-time {
        font-size: 11px;
        color: var(--text-ghost);
        margin-top: 2px;
    }

    .evaluation-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }

    .evaluation-form-grid {
        display: grid;
        gap: 12px;
    }

    .evaluation-help {
        font-size: 11.5px;
        color: var(--text-muted);
        margin-top: 5px;
        line-height: 1.5;
    }

    @media (max-width: 1180px) {
        .evaluation-body {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        .evaluation-card-header {
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
<div class="pdp-notice">
    <i class="fas fa-clipboard-check"></i>
    <div><strong>Evaluasi Pemanfaatan:</strong> Unggah hasil tindak lanjut atau laporan pemanfaatan untuk data yang sudah pernah berhasil diunduh.</div>
</div>

<div class="evaluation-list">
    @forelse($requests as $request)
        @php
            $evaluation = $request->utilizationEvaluation;
            $latestDownload = $request->downloadLogs->first();
        @endphp

        <div class="evaluation-card">
            <div class="evaluation-card-header">
                <div>
                    <div class="evaluation-title">{{ $request->dataFile->judul ?? '-' }}</div>
                    <div class="evaluation-meta">
                        @if($request->dataFile?->kategori_label || $request->dataFile?->kategori)
                            <span><i class="fas fa-tag"></i> {{ $request->dataFile->kategori_label ?? $request->dataFile->kategori }}</span>
                        @endif
                        @if($request->dataFile?->tahun_data)
                            <span><i class="fas fa-calendar"></i> {{ $request->dataFile->tahun_data }}</span>
                        @endif
                        <span><i class="fas fa-download"></i> {{ $request->downloadLogs->count() }}x unduh</span>
                    </div>
                </div>

                @if($evaluation)
                    <span class="badge badge-bast_approved">Sudah diunggah</span>
                @else
                    <span class="badge badge-pending">Belum diunggah</span>
                @endif
            </div>

            <div class="evaluation-body">
                <div class="evaluation-section">
                    <div class="evaluation-section-title"><i class="fas fa-clock-rotate-left"></i> Riwayat Unduh</div>
                    <div class="info-row">
                        <div class="info-label">Jumlah unduhan berhasil</div>
                        <div class="info-value">{{ $request->downloadLogs->count() }} kali</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Unduhan terakhir</div>
                        <div class="info-value">{{ $latestDownload?->downloaded_at?->format('d/m/Y H:i') ?? '-' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Status akses</div>
                        <div class="info-value"><span class="badge badge-{{ $request->status }}">{{ $request->status_label }}</span></div>
                    </div>
                </div>

                <div class="evaluation-section">
                    <div class="evaluation-section-title"><i class="fas fa-paperclip"></i> Dokumen Saat Ini</div>
                    @if($evaluation)
                        <div class="evaluation-file">
                            <div class="evaluation-file-icon"><i class="fas fa-file-lines"></i></div>
                            <div>
                                <div class="evaluation-file-name">{{ $evaluation->report_filename }}</div>
                                <div class="evaluation-file-time">Diunggah {{ $evaluation->submitted_at?->format('d/m/Y H:i') ?? '-' }}</div>
                            </div>
                        </div>

                        @if($evaluation->notes)
                            <div class="text-block" style="font-size:12px;margin-bottom:12px;">
                                {{ $evaluation->notes }}
                            </div>
                        @endif

                        <div class="evaluation-actions">
                            <a href="{{ route('admin.evaluations.download', $evaluation) }}" class="btn btn-sm btn-outline">
                                <i class="fas fa-download"></i> Download Lampiran
                            </a>
                        </div>
                    @else
                        <div class="empty-state" style="padding:22px 12px;">
                            <div class="empty-icon" style="width:46px;height:46px;font-size:18px;margin-bottom:10px;"><i class="fas fa-file-circle-plus"></i></div>
                            <h3>Belum ada lampiran</h3>
                            <p>Upload laporan pemanfaatan data melalui form di samping.</p>
                        </div>
                    @endif
                </div>

                <div class="evaluation-section">
                    <div class="evaluation-section-title"><i class="fas fa-upload"></i> {{ $evaluation ? 'Perbarui Evaluasi' : 'Upload Evaluasi' }}</div>
                    <form action="{{ route('admin.evaluations.store', $request) }}" method="POST" enctype="multipart/form-data" class="evaluation-form-grid">
                        @csrf
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" for="report_file_{{ $request->id }}">Dokumen lampiran</label>
                            <input id="report_file_{{ $request->id }}" type="file" name="report_file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                            <div class="evaluation-help">Format PDF, DOC, DOCX, XLS, atau XLSX. Ukuran maksimal 10MB.</div>
                            @error('report_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" for="notes_{{ $request->id }}">Catatan tindak lanjut</label>
                            <textarea id="notes_{{ $request->id }}" name="notes" class="form-control" rows="4" placeholder="Tuliskan ringkasan pemanfaatan data atau tindak lanjut yang sudah dilakukan.">{{ old('notes', $evaluation->notes ?? '') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="evaluation-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> {{ $evaluation ? 'Perbarui Lampiran' : 'Upload Lampiran' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-clipboard-list"></i></div>
                <h3>Belum ada data yang perlu dievaluasi</h3>
                <p>Daftar ini akan terisi setelah ada data yang disetujui dan berhasil diunduh.</p>
            </div>
        </div>
    @endforelse
</div>

@if($requests->hasPages())
    <div style="padding:18px 0 0;">{{ $requests->links() }}</div>
@endif
@endsection
