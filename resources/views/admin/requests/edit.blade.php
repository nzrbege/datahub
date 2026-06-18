@extends('layouts.app')
@section('page-title', 'Revisi Permintaan #' . $dataRequest->id)

@section('content')
<div style="margin-bottom:20px;">
    <a href="{{ route('admin.requests.show', $dataRequest) }}" class="btn btn-ghost btn-sm">
        <i class="fas fa-arrow-left"></i> Kembali ke Detail Permintaan
    </a>
</div>

<div class="pdp-notice">
    <i class="fas fa-file-pen"></i>
    <div><strong>Revisi Permintaan:</strong> Perbaiki alasan, tujuan, dasar hukum, dan lampirkan ulang NDA. Setelah dikirim, status permintaan kembali menjadi menunggu review Super Admin.</div>
</div>

<div style="display:grid; grid-template-columns:3fr 2fr; gap:20px; align-items:start;">
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-file-pen"></i> Form Revisi Permintaan</span>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.requests.update', $dataRequest) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="info-grid" style="margin-bottom:18px;">
                    <div class="info-row">
                        <div class="info-label">Dataset</div>
                        <div class="info-value" style="font-weight:700;">{{ $dataRequest->dataFile->judul ?? '—' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">NDA Sebelumnya</div>
                        <div class="info-value"><i class="fas fa-file-pdf" style="color:#dc2626;"></i> {{ $dataRequest->nda_filename }}</div>
                    </div>
                </div>

                @if($dataRequest->catatan_reviewer)
                <div class="alert alert-danger">
                    <i class="fas fa-comment-dots"></i>
                    <div>
                        <strong>Catatan Penolakan:</strong><br>
                        {{ $dataRequest->catatan_reviewer }}
                    </div>
                </div>
                @endif

                <div class="form-group">
                    <label class="form-label">Dasar Hukum Penggunaan <span class="required">*</span></label>
                    <input type="text" name="dasar_hukum" class="form-control" value="{{ old('dasar_hukum', $dataRequest->dasar_hukum) }}" required>
                    @error('dasar_hukum')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Dokumen NDA Baru <span class="required">*</span></label>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div id="ndaDropZone" style="flex:1; border:1.5px dashed var(--border-dark); border-radius:var(--radius-sm); padding:14px 16px; cursor:pointer; display:flex; align-items:center; gap:10px; transition:all .15s; background:var(--surface-2);"
                            onclick="document.getElementById('ndaInput').click()">
                            <i class="fas fa-file-pdf" style="color:#dc2626; font-size:18px;"></i>
                            <div>
                                <div id="ndaLabel" style="font-size:13px; font-weight:600; color:var(--text-2);">Klik untuk memilih file PDF revisi</div>
                                <div style="font-size:11px; color:var(--text-muted);">Wajib unggah ulang NDA PDF &bull; Maks 5MB</div>
                            </div>
                        </div>
                        <input type="file" name="nda_file" id="ndaInput" accept=".pdf" style="display:none;" required onchange="showNda(this)">
                    </div>
                    @error('nda_file')<div class="invalid-feedback" style="display:block; margin-top:4px;">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Alasan Permintaan <span class="required">*</span></label>
                    <textarea name="alasan_permintaan" class="form-control" rows="4" required>{{ old('alasan_permintaan', $dataRequest->alasan_permintaan) }}</textarea>
                    @error('alasan_permintaan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Tujuan Penggunaan Data <span class="required">*</span></label>
                    <textarea name="tujuan_penggunaan" class="form-control" rows="4" required>{{ old('tujuan_penggunaan', $dataRequest->tujuan_penggunaan) }}</textarea>
                    @error('tujuan_penggunaan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div style="display:flex; gap:10px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Kirim Revisi
                    </button>
                    <a href="{{ route('admin.requests.show', $dataRequest) }}" class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-circle-info"></i> Setelah Revisi</span>
        </div>
        <div class="card-body" style="font-size:13px; line-height:1.7; color:var(--text-2);">
            <p>Revisi akan mengubah status permintaan menjadi <strong>Menunggu Persetujuan</strong>.</p>
            <p style="margin-top:10px;">Super Admin akan melihat isian dan NDA terbaru untuk review ulang.</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showNda(input) {
    const f = input.files[0];
    if (!f) return;
    const size = (f.size/1024).toFixed(0);
    document.getElementById('ndaLabel').textContent = f.name + ' - ' + size + ' KB';
    document.getElementById('ndaDropZone').style.borderColor = 'var(--success)';
    document.getElementById('ndaDropZone').style.background = 'var(--success-bg)';
}
</script>
@endpush
@endsection
