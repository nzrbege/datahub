@extends('layouts.app')
@section('page-title', 'Unggah Dataset Baru')

@section('content')
<div style="margin-bottom:20px;">
    <a href="{{ route('superadmin.files.index') }}" class="btn btn-ghost btn-sm">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Dataset
    </a>
</div>

<div class="pdp-notice">
    <i class="fas fa-shield-halved"></i>
    <div><strong>UU PDP Pasal 35 - Keamanan Data:</strong> Pastikan file sudah diberi token/password sebelum diunggah. Sistem menyimpan file di storage privat dan membatasi akses OPD sesuai izin yang diberikan.</div>
</div>

<form action="{{ route('superadmin.files.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div style="display:grid; grid-template-columns:3fr 2fr; gap:20px; align-items:start;">

        <!-- Left: Metadata -->
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-table"></i> Informasi Dataset</span>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Judul Dataset <span class="required">*</span></label>
                    <input type="text" name="judul" class="form-control" value="{{ old('judul') }}"
                        placeholder="mis: Data Keluarga Kecamatan Prambanan 2024" required>
                    @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0 16px;">
                    <div class="form-group">
                        <label class="form-label">Jenis Dataset <span class="required">*</span></label>
                        <select name="kategori" class="form-control" required>
                            <option value="">— Pilih Jenis —</option>
                            <option value="DATASET_KELUARGA" {{ old('kategori')=='DATASET_KELUARGA'?'selected':'' }}>Dataset Keluarga</option>
                            <option value="DATASET_ANGGOTA_KELUARGA" {{ old('kategori')=='DATASET_ANGGOTA_KELUARGA'?'selected':'' }}>Dataset Anggota Keluarga</option>
                        </select>
                        @error('kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Periode Data</label>
                        <input type="text" name="tahun_data" class="form-control"
                            value="{{ old('tahun_data', date('Y')) }}" placeholder="mis: 2024 atau 2024-06">
                        <div class="form-text">Tahun saja atau tahun-bulan.</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi Dataset</label>
                    <textarea name="deskripsi" class="form-control" rows="3"
                        placeholder="Deskripsi singkat isi, sumber, dan tujuan penggunaan dataset...">{{ old('deskripsi') }}</textarea>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">File Dataset <span class="required">*</span></label>
                    <div id="dropZone" style="border:2px dashed var(--border-dark); border-radius:var(--radius); padding:28px; text-align:center; cursor:pointer; transition:all .2s; background:var(--surface-2);"
                        onclick="document.getElementById('fileInput').click()"
                        ondragover="event.preventDefault(); this.style.borderColor='var(--brand-400)'; this.style.background='var(--brand-50)';"
                        ondragleave="this.style.borderColor='var(--border-dark)'; this.style.background='var(--surface-2)';"
                        ondrop="handleDrop(event)">
                        <i class="fas fa-cloud-arrow-up" style="font-size:28px; color:var(--brand-400); display:block; margin-bottom:10px;"></i>
                        <div style="font-size:13.5px; font-weight:700; color:var(--text-2);">Klik atau drag &amp; drop file di sini</div>
                        <div style="font-size:11.5px; color:var(--text-muted); margin-top:5px;">Format: XLSX, CSV, ZIP &bull; Maks 50MB &bull; Pastikan file sudah diberi token/password</div>
                    </div>
                    <input type="file" name="file" id="fileInput" accept=".xlsx,.csv,.zip" style="display:none;" required onchange="showFileInfo(this)">
                    <div id="fileInfo" style="display:none; margin-top:10px; padding:10px 13px; background:var(--success-bg); border:1px solid var(--success-border); border-radius:var(--radius-sm); font-size:12.5px; color:#065f46; display:flex; align-items:center; gap:8px;">
                        <i class="fas fa-circle-check"></i>
                        <span id="fileInfoText"></span>
                    </div>
                    @error('file')<div class="invalid-feedback" style="display:block; margin-top:5px;">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <!-- Right: Permissions -->
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-user-shield"></i> Izin Akses OPD</span>
            </div>
            <div class="card-body">
                <p style="font-size:12.5px; color:var(--text-muted); margin-bottom:14px; line-height:1.65;">
                    Pilih OPD yang diizinkan melihat dan mengunduh dataset ini. OPD yang tidak dicentang <strong>tidak akan bisa</strong> mengakses sama sekali.
                </p>

                @if($admins->isEmpty())
                    <div class="alert alert-warning">
                        <i class="fas fa-triangle-exclamation"></i>
                        <div>Belum ada admin OPD yang terdaftar. Tambahkan pengguna terlebih dahulu.</div>
                    </div>
                @else
                    <div style="display:flex; flex-direction:column; gap:8px; max-height:400px; overflow-y:auto; padding-right:4px;">
                        @foreach($admins as $admin)
                        <label class="admin-check-item" style="display:flex; align-items:flex-start; gap:10px; padding:11px 13px; border:1.5px solid var(--border); border-radius:var(--radius-sm); cursor:pointer; font-size:12.5px; transition:all .15s;"
                            onmouseover="this.style.borderColor='var(--brand-300)'; this.style.background='var(--brand-50)'"
                            onmouseout="checkStyle(this)">
                            <input type="checkbox" name="admin_ids[]" value="{{ $admin->id }}"
                                {{ in_array($admin->id, old('admin_ids', [])) ? 'checked' : '' }}
                                style="margin-top:2px; accent-color:var(--brand-500); cursor:pointer; width:14px; height:14px;"
                                onchange="checkStyle(this.parentElement)">
                            <div>
                                <div style="font-weight:700; color:var(--text);">{{ $admin->name }}</div>
                                <div style="font-size:11px; color:var(--text-muted); margin-top:1px;">
                                    <i class="fas fa-building" style="font-size:9px;"></i> {{ $admin->instansi }}
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    <div class="form-text" style="margin-top:10px;">Tidak memilih berarti dataset hanya bisa diakses Super Admin.</div>
                @endif

                <div style="border-top:1px solid var(--border); margin-top:20px; padding-top:18px; display:flex; gap:10px;">
                    <button type="submit" class="btn btn-primary" style="flex:1; justify-content:center;">
                        <i class="fas fa-cloud-arrow-up"></i> Unggah Dataset
                    </button>
                    <a href="{{ route('superadmin.files.index') }}" class="btn btn-ghost">Batal</a>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
function showFileInfo(input) {
    const f = input.files[0];
    if (!f) return;
    const size = f.size > 1024*1024 ? (f.size/1024/1024).toFixed(1)+' MB' : (f.size/1024).toFixed(0)+' KB';
    const info = document.getElementById('fileInfo');
    document.getElementById('fileInfoText').textContent = f.name + ' - ' + size + ' - akan disimpan di storage privat';
    info.style.display = 'flex';
    document.getElementById('dropZone').style.borderColor = 'var(--success)';
    document.getElementById('dropZone').style.background = 'var(--success-bg)';
}
function handleDrop(e) {
    e.preventDefault();
    document.getElementById('dropZone').style.borderColor = 'var(--border-dark)';
    document.getElementById('dropZone').style.background = 'var(--surface-2)';
    const files = e.dataTransfer.files;
    if (files.length) {
        document.getElementById('fileInput').files = files;
        showFileInfo(document.getElementById('fileInput'));
    }
}
function checkStyle(el) {
    const cb = el.querySelector('input[type=checkbox]');
    if (cb && cb.checked) {
        el.style.borderColor = 'var(--brand-400)';
        el.style.background = 'var(--brand-50)';
    } else {
        el.style.borderColor = 'var(--border)';
        el.style.background = '';
    }
}
document.querySelectorAll('.admin-check-item').forEach(el => checkStyle(el));
</script>
@endpush
@endsection
