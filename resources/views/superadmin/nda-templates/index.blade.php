@extends('layouts.app')
@section('page-title', 'Template NDA')

@section('content')
<div class="pdp-notice">
    <i class="fas fa-file-contract"></i>
    <div><strong>Template NDA Aktif:</strong> File yang diunggah di halaman ini akan menjadi template yang dapat diunduh Admin OPD saat mengajukan permintaan akses data.</div>
</div>

<div style="display:grid; grid-template-columns:2fr 3fr; gap:20px; align-items:start;">
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-cloud-arrow-up"></i> Unggah Template Baru</span>
        </div>
        <div class="card-body">
            @if($activeTemplate)
                <div style="background:var(--success-bg); border:1px solid var(--success-border); border-radius:var(--radius-sm); padding:13px 15px; margin-bottom:18px;">
                    <div style="font-size:12px; font-weight:700; color:#065f46; margin-bottom:4px;">Template aktif saat ini</div>
                    <div style="font-size:13px; color:var(--text); font-weight:600;">{{ $activeTemplate->original_filename }}</div>
                    <div style="font-size:11px; color:var(--text-muted); margin-top:3px;">
                        {{ $activeTemplate->file_size_human }} &bull; diunggah {{ $activeTemplate->created_at->format('d/m/Y H:i') }}
                    </div>
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-triangle-exclamation"></i>
                    <div>Belum ada template NDA aktif.</div>
                </div>
            @endif

            <form action="{{ route('superadmin.nda-templates.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label class="form-label">File Template NDA <span class="required">*</span></label>
                    <div id="templateDropZone" style="border:1.5px dashed var(--border-dark); border-radius:var(--radius-sm); padding:18px 16px; cursor:pointer; display:flex; align-items:center; gap:12px; background:var(--surface-2);"
                        onclick="document.getElementById('templateInput').click()">
                        <i class="fas fa-file-lines" style="color:var(--brand-500); font-size:20px;"></i>
                        <div>
                            <div id="templateLabel" style="font-size:13px; font-weight:700; color:var(--text-2);">Pilih file PDF/DOC/DOCX</div>
                            <div style="font-size:11px; color:var(--text-muted);">Maks 5MB. Template baru otomatis menggantikan template aktif.</div>
                        </div>
                    </div>
                    <input type="file" name="template_file" id="templateInput" accept=".pdf,.doc,.docx" style="display:none;" required onchange="showTemplateFile(this)">
                    @error('template_file')<div class="invalid-feedback" style="display:block;">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">
                    <i class="fas fa-cloud-arrow-up"></i> Simpan Template NDA
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-clock-rotate-left"></i> Riwayat Template</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Status</th>
                        <th>Pengunggah</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $template)
                    <tr>
                        <td>
                            <div style="font-weight:700; color:var(--text);">{{ $template->original_filename }}</div>
                            <div style="font-size:11px; color:var(--text-muted);">{{ strtoupper($template->file_type) }} &bull; {{ $template->file_size_human }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $template->is_active ? 'badge-approved' : 'badge-revoked' }}">
                                {{ $template->is_active ? 'Aktif' : 'Arsip' }}
                            </span>
                        </td>
                        <td>{{ $template->uploader->name ?? '-' }}</td>
                        <td>
                            <div style="font-size:12.5px; font-weight:600;">{{ $template->created_at->format('d/m/Y') }}</div>
                            <div style="font-size:11px; color:var(--text-ghost); font-family:'JetBrains Mono',monospace;">{{ $template->created_at->format('H:i:s') }}</div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty-state" style="padding:34px 24px;">
                                <div class="empty-icon"><i class="fas fa-file-contract"></i></div>
                                <h3>Belum ada template NDA</h3>
                                <p>Unggah template pertama agar Admin OPD dapat mengunduhnya dari form pengajuan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($templates->hasPages())
            <div style="padding:0 22px 18px;">{{ $templates->links() }}</div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function showTemplateFile(input) {
    const f = input.files[0];
    if (!f) return;
    const size = f.size > 1024*1024 ? (f.size/1024/1024).toFixed(1)+' MB' : (f.size/1024).toFixed(0)+' KB';
    document.getElementById('templateLabel').textContent = f.name + ' - ' + size;
    document.getElementById('templateDropZone').style.borderColor = 'var(--success)';
    document.getElementById('templateDropZone').style.background = 'var(--success-bg)';
}
</script>
@endpush
@endsection
