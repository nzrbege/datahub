@extends('layouts.app')
@section('page-title', 'Ajukan Permintaan Akses Data')

@section('content')
<div style="margin-bottom:20px;">
    <a href="{{ route('admin.requests.index') }}" class="btn btn-ghost btn-sm">
        <i class="fas fa-arrow-left"></i> Kembali ke Permintaan Saya
    </a>
</div>

<div class="pdp-notice">
    <i class="fas fa-scale-balanced"></i>
    <div><strong>Permohonan Akses Data:</strong> Permohonan akses data pribadi wajib menyertakan alasan dan tujuan penggunaan yang jelas serta dokumen permohonan resmi dalam format PDF. Seluruh akses akan tercatat dalam audit log.</div>
</div>

<div style="display:grid; grid-template-columns:3fr 2fr; gap:20px; align-items:start;">
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-file-signature"></i> Formulir Permintaan Akses Data</span>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.requests.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div>
                    <div class="form-group">
                        <label class="form-label">Dataset yang Diminta <span class="required">*</span></label>
                        @if($selectedFile)
                            <input type="hidden" name="data_file_id" value="{{ $selectedFile->id }}">
                            <div style="border:1.5px solid var(--border); border-radius:var(--radius-sm); padding:14px 16px; background:var(--surface-2);">
                                <div style="font-size:13.5px; font-weight:800; color:var(--text);">{{ $selectedFile->judul }}</div>
                                <div style="font-size:11.5px; color:var(--text-muted); margin-top:4px;">
                                    {{ $selectedFile->kategori_label }} &bull; {{ $selectedFile->tahun_data ?? '-' }} &bull; {{ $selectedFile->file_size_human }}
                                </div>
                            </div>
                        @else
                        <select name="data_file_id" class="form-control" required>
                            <option value="">— Pilih Dataset —</option>
                            @foreach($availableFiles as $file)
                            <option value="{{ $file->id }}" {{ old('data_file_id')==$file->id?'selected':'' }}>
                                {{ $file->judul }} ({{ $file->kategori_label }}, {{ $file->tahun_data ?? '—' }})
                            </option>
                            @endforeach
                        </select>
                        @endif
                        @if($availableFiles->isEmpty())
                        <div class="form-text" style="color:var(--danger);">
                            Tidak ada dataset tersedia. Hubungi Super Admin untuk meminta akses.
                        </div>
                        @endif
                        @error('data_file_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                </div>

                <div class="form-group">
                    <label class="form-label">Dokumen Permohonan <span class="required">*</span></label>
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:8px;">
                        <div class="form-text" style="margin-top:0;">Unggah surat/dokumen permohonan resmi yang sudah ditandatangani dan distempel instansi.</div>
                        <a href="{{ route('admin.templates.download', ['type' => \App\Models\NdaTemplate::TYPE_REQUEST_LETTER]) }}" class="btn btn-xs btn-outline">
                            <i class="fas fa-download"></i> Template Surat Permohonan
                        </a>
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div id="ndaDropZone" style="flex:1; border:1.5px dashed var(--border-dark); border-radius:var(--radius-sm); padding:14px 16px; cursor:pointer; display:flex; align-items:center; gap:10px; transition:all .15s; background:var(--surface-2);"
                            onclick="document.getElementById('ndaInput').click()">
                            <i class="fas fa-file-pdf" style="color:#dc2626; font-size:18px;"></i>
                            <div>
                                <div id="ndaLabel" style="font-size:13px; font-weight:600; color:var(--text-2);">Klik untuk memilih file PDF</div>
                                <div style="font-size:11px; color:var(--text-muted);">Dokumen permohonan PDF &bull; Maks 5MB</div>
                            </div>
                        </div>
                        <input type="file" name="request_file" id="ndaInput" accept=".pdf" style="display:none;" required
                            onchange="showNda(this)">
                    </div>
                    @error('request_file')<div class="invalid-feedback" style="display:block; margin-top:4px;">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Alasan dan Tujuan Penggunaan <span class="required">*</span></label>
                    <textarea name="alasan_permintaan" class="form-control" rows="4" required
                        placeholder="Jelaskan mengapa data dibutuhkan, untuk apa digunakan, siapa yang mengakses, dan bagaimana data disimpan setelah selesai (min. 50 karakter)...">{{ old('alasan_permintaan') }}</textarea>
                    <div class="form-text">Tuliskan kebutuhan dan rencana penggunaan data dalam satu penjelasan.</div>
                    @error('alasan_permintaan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <!-- Pernyataan -->
                <div style="background:var(--surface-2); border:1.5px solid var(--border); border-radius:var(--radius-sm); padding:16px; margin-bottom:20px;">
                    <div style="font-size:12.5px; font-weight:700; color:var(--text-2); margin-bottom:10px;">
                        <i class="fas fa-file-contract" style="color:var(--brand-500);"></i> Pernyataan Pemohon
                    </div>
                    <label class="checkbox-wrap" style="align-items:flex-start; gap:10px;">
                        <input type="checkbox" required style="margin-top:2px;">
                        <span style="font-size:12.5px; line-height:1.65; color:var(--text-2);">Saya menyatakan bahwa permohonan ini diajukan atas kepentingan tugas resmi instansi, data yang diterima tidak akan dibagikan kepada pihak yang tidak berwenang, dan saya tunduk pada ketentuan <strong>UU PDP No.27/2022</strong> serta dokumen BAST yang akan dilampirkan setelah permohonan disetujui.</span>
                    </label>
                </div>

                <div style="display:flex; gap:10px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Kirim Permintaan
                    </button>
                    <a href="{{ route('admin.requests.index') }}" class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Right: Help -->
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-circle-question"></i> Panduan Pengajuan</span>
        </div>
        <div class="card-body">
            <div class="flow-steps">
                <div class="flow-step">
                    <div class="flow-num">1</div>
                    <div class="flow-text">
                        <strong>{{ $selectedFile ? 'Dataset terpilih' : 'Pilih dataset' }}</strong>
                        <span>{{ $selectedFile ? 'Dataset sudah mengikuti kartu yang Anda pilih dari dashboard' : 'Pilih dataset yang diperlukan dari daftar yang tersedia untuk instansi Anda' }}</span>
                    </div>
                </div>
                <div class="flow-step">
                    <div class="flow-num">2</div>
                    <div class="flow-text">
                        <strong>Isi alasan &amp; tujuan</strong>
                        <span>Jelaskan secara rinci kebutuhan dan rencana penggunaan data</span>
                    </div>
                </div>
                <div class="flow-step">
                    <div class="flow-num">3</div>
                    <div class="flow-text">
                        <strong>Unggah permohonan</strong>
                        <span>Lampirkan dokumen permohonan resmi dalam format PDF</span>
                    </div>
                </div>
                <div class="flow-step">
                    <div class="flow-num">4</div>
                    <div class="flow-text">
                        <strong>Tunggu persetujuan</strong>
                        <span>Super Admin akan meninjau dan memutuskan permintaan dalam 1-3 hari kerja</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showNda(input) {
    const f = input.files[0];
    if (!f) return;
    const size = (f.size/1024).toFixed(0);
    document.getElementById('ndaLabel').textContent = f.name + ' · ' + size + ' KB';
    document.getElementById('ndaDropZone').style.borderColor = 'var(--success)';
    document.getElementById('ndaDropZone').style.background = 'var(--success-bg)';
}
</script>
@endpush
@endsection
