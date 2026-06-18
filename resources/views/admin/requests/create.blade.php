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
    <div><strong>Dasar Hukum UU PDP No.27/2022:</strong> Permohonan akses data pribadi wajib menyertakan alasan yang jelas, dasar hukum penggunaan, tujuan pemrosesan, dan Perjanjian Kerahasiaan (NDA) yang ditandatangani. Seluruh akses akan tercatat dalam audit log.</div>
</div>

<div style="display:grid; grid-template-columns:3fr 2fr; gap:20px; align-items:start;">
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-file-signature"></i> Formulir Permintaan Akses Data</span>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.requests.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0 16px;">
                    <div class="form-group">
                        <label class="form-label">Dataset yang Diminta <span class="required">*</span></label>
                        <select name="data_file_id" class="form-control" required>
                            <option value="">— Pilih Dataset —</option>
                            @foreach($availableFiles as $file)
                            <option value="{{ $file->id }}" {{ old('data_file_id')==$file->id?'selected':'' }}>
                                {{ $file->judul }} ({{ $file->kategori_label }}, {{ $file->tahun_data ?? '—' }})
                            </option>
                            @endforeach
                        </select>
                        @if($availableFiles->isEmpty())
                        <div class="form-text" style="color:var(--danger);">
                            Tidak ada dataset tersedia. Hubungi Super Admin untuk meminta akses.
                        </div>
                        @endif
                        @error('data_file_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Dasar Hukum Penggunaan <span class="required">*</span></label>
                        <input type="text" name="dasar_hukum" class="form-control" value="{{ old('dasar_hukum') }}"
                            placeholder="mis: Permendagri No.X/2024 Pasal Y">
                        <div class="form-text">Cantumkan peraturan yang menjadi dasar penggunaan data.</div>
                        @error('dasar_hukum')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Dokumen NDA (Perjanjian Kerahasiaan) <span class="required">*</span></label>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div id="ndaDropZone" style="flex:1; border:1.5px dashed var(--border-dark); border-radius:var(--radius-sm); padding:14px 16px; cursor:pointer; display:flex; align-items:center; gap:10px; transition:all .15s; background:var(--surface-2);"
                            onclick="document.getElementById('ndaInput').click()">
                            <i class="fas fa-file-pdf" style="color:#dc2626; font-size:18px;"></i>
                            <div>
                                <div id="ndaLabel" style="font-size:13px; font-weight:600; color:var(--text-2);">Klik untuk memilih file PDF</div>
                                <div style="font-size:11px; color:var(--text-muted);">Dokumen NDA bertandatangan &amp; stempel instansi &bull; Maks 5MB</div>
                            </div>
                        </div>
                        <input type="file" name="nda_file" id="ndaInput" accept=".pdf" style="display:none;" required
                            onchange="showNda(this)">
                    </div>
                    @error('nda_file')<div class="invalid-feedback" style="display:block; margin-top:4px;">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Alasan Permintaan <span class="required">*</span></label>
                    <textarea name="alasan_permintaan" class="form-control" rows="4" required
                        placeholder="Jelaskan secara detail mengapa data ini dibutuhkan (min. 50 karakter)...">{{ old('alasan_permintaan') }}</textarea>
                    @error('alasan_permintaan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Tujuan Penggunaan Data <span class="required">*</span></label>
                    <textarea name="tujuan_penggunaan" class="form-control" rows="4" required
                        placeholder="Jelaskan untuk apa data akan digunakan, siapa yang mengaksesnya, dan bagaimana data akan disimpan &amp; dihapus setelah selesai (min. 50 karakter)...">{{ old('tujuan_penggunaan') }}</textarea>
                    <div class="form-text">Sesuai UU PDP Pasal 20: tujuan pemrosesan harus spesifik, eksplisit, dan sah.</div>
                    @error('tujuan_penggunaan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <!-- Pernyataan -->
                <div style="background:var(--surface-2); border:1.5px solid var(--border); border-radius:var(--radius-sm); padding:16px; margin-bottom:20px;">
                    <div style="font-size:12.5px; font-weight:700; color:var(--text-2); margin-bottom:10px;">
                        <i class="fas fa-file-contract" style="color:var(--brand-500);"></i> Pernyataan Pemohon
                    </div>
                    <label class="checkbox-wrap" style="align-items:flex-start; gap:10px;">
                        <input type="checkbox" required style="margin-top:2px;">
                        <span style="font-size:12.5px; line-height:1.65; color:var(--text-2);">Saya menyatakan bahwa permohonan ini diajukan atas kepentingan tugas resmi instansi, data yang diterima tidak akan dibagikan kepada pihak yang tidak berwenang, dan saya tunduk pada ketentuan <strong>UU PDP No.27/2022</strong> serta perjanjian kerahasiaan yang dilampirkan.</span>
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
                        <strong>Pilih dataset</strong>
                        <span>Pilih dataset yang diperlukan dari daftar yang tersedia untuk instansi Anda</span>
                    </div>
                </div>
                <div class="flow-step">
                    <div class="flow-num">2</div>
                    <div class="flow-text">
                        <strong>Cantumkan dasar hukum</strong>
                        <span>Sebutkan peraturan/regulasi yang menjadi dasar kebutuhan data</span>
                    </div>
                </div>
                <div class="flow-step">
                    <div class="flow-num">3</div>
                    <div class="flow-text">
                        <strong>Unggah NDA</strong>
                        <span>Lampirkan dokumen NDA yang sudah ditandatangani pimpinan dan dicap basah</span>
                    </div>
                </div>
                <div class="flow-step">
                    <div class="flow-num">4</div>
                    <div class="flow-text">
                        <strong>Isi alasan &amp; tujuan</strong>
                        <span>Jelaskan secara rinci kebutuhan dan rencana penggunaan data</span>
                    </div>
                </div>
                <div class="flow-step" style="padding-bottom:0;">
                    <div class="flow-num">5</div>
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
