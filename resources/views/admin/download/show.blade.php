@extends('layouts.app')
@section('page-title', 'Unduh File Data')

@section('content')
<div style="max-width:520px; margin:0 auto;">
    <div class="pdp-notice">
        <i class="fas fa-lock"></i>
        <div><strong>Peringatan UU PDP:</strong> Anda akan mengunduh data pribadi yang dilindungi undang-undang. Penggunaan di luar tujuan yang disetujui merupakan pelanggaran hukum. Aktivitas ini dicatat secara permanen dalam audit log.</div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-download"></i> Konfirmasi Unduhan</span>
        </div>
        <div class="card-body">
            <!-- Info File -->
            <div style="background:var(--surface-2); border:1px solid var(--border); border-radius:var(--radius-sm); padding:16px; margin-bottom:22px;">
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:14px;">
                    <div style="width:42px; height:42px; border-radius:var(--radius-sm); background:var(--brand-100); color:var(--brand-600); display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0;">
                        <i class="fas fa-table"></i>
                    </div>
                    <div>
                        <div style="font-weight:800; font-size:14px;">{{ $dataRequest->dataFile->judul }}</div>
                        <div style="font-size:12px; color:var(--text-muted);">{{ $dataRequest->dataFile->kategori_label }}</div>
                    </div>
                </div>
                <div class="quick-item" style="padding:8px 0;">
                    <span style="font-size:12.5px; color:var(--text-muted);">Ukuran File</span>
                    <strong style="font-size:13px; font-family:'JetBrains Mono',monospace;">{{ $dataRequest->dataFile->file_size_human }}</strong>
                </div>
                <div class="quick-item" style="padding:8px 0;">
                    <span style="font-size:12.5px; color:var(--text-muted);">Sisa Unduhan {{ ucfirst($dataRequest->quotaWindowLabel()) }}</span>
                    <strong style="font-size:14px; color:var(--danger);">{{ $dataRequest->remainingDownloads() }}x dari {{ $dataRequest->max_downloads }}x</strong>
                </div>
                <div class="quick-item" style="padding:8px 0; border-bottom:none;">
                    <span style="font-size:12.5px; color:var(--text-muted);">Kuota {{ $dataRequest->quotaPeriodLabel() }}</span>
                    <strong style="font-size:12.5px;">
                        {{ $dataRequest->quotaPeriod() === 'lifetime' ? 'Tidak reset otomatis' : $dataRequest->token_expires_at?->format('d/m/Y H:i') }}
                    </strong>
                </div>
            </div>

            <!-- Captcha -->
            <form action="{{ route('admin.download.process', $dataRequest) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-robot" style="color:var(--text-ghost);"></i>
                        Verifikasi Captcha <span class="required">*</span>
                    </label>
                    <div style="margin-bottom:10px; display:flex; align-items:center; gap:10px;">
                        <img src="{{ captcha_src() }}" alt="Captcha" id="captchaImg"
                            style="border:1.5px solid var(--border); border-radius:var(--radius-sm); cursor:pointer; display:block;"
                            title="Klik untuk refresh" onclick="refreshCaptcha()">
                        <button type="button" onclick="refreshCaptcha()" class="btn btn-ghost btn-sm">
                            <i class="fas fa-rotate"></i> Refresh
                        </button>
                    </div>
                    <input type="text" name="captcha" class="form-control"
                        placeholder="Masukkan kode di atas" autocomplete="off" required>
                    @error('captcha')
                    <div class="invalid-feedback" style="display:block;">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Masukkan kode yang terlihat pada gambar. Case-sensitive.</div>
                </div>

                <div style="background:var(--warning-bg); border:1.5px solid var(--warning-border); border-radius:var(--radius-sm); padding:13px 15px; font-size:12.5px; color:#78350f; margin-bottom:18px; display:flex; gap:9px; align-items:flex-start;">
                    <i class="fas fa-triangle-exclamation" style="color:var(--warning); flex-shrink:0; margin-top:1px;"></i>
                    <div>Dengan mengunduh file ini, Anda mengonfirmasi bahwa penggunaan data sesuai dengan permohonan yang disetujui dan Perjanjian Kerahasiaan yang telah ditandatangani.</div>
                </div>

                <button type="submit" class="btn btn-success" style="width:100%; justify-content:center; font-size:14px; padding:12px;">
                    <i class="fas fa-download"></i> Unduh File Sekarang
                </button>
            </form>
        </div>
    </div>

    <div style="text-align:center; margin-top:14px;">
        <a href="{{ route('admin.requests.show', $dataRequest) }}" class="btn btn-ghost btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali ke Detail Permintaan
        </a>
    </div>
</div>

@push('scripts')
<script>
function refreshCaptcha() {
    const img = document.getElementById('captchaImg');
    img.src = img.src.split('?')[0] + '?r=' + Math.random();
}
</script>
@endpush
@endsection
