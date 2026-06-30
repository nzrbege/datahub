@extends('layouts.app')
@section('page-title', 'Template Dokumen')

@section('content')
<div class="pdp-notice">
    <i class="fas fa-file-contract"></i>
    <div><strong>Template Dokumen:</strong> Super Admin dapat mengunggah template Surat Permohonan Data dan template BAST yang dapat diunduh Admin OPD.</div>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:20px; align-items:start;">
    @foreach($typeOptions as $type => $label)
        @php($activeTemplate = $activeTemplates[$type] ?? null)
        <div class="card">
            <div class="card-header">
                <span class="card-title">
                    <i class="{{ $type === \App\Models\NdaTemplate::TYPE_BAST ? 'fas fa-file-signature' : 'fas fa-file-lines' }}"></i>
                    {{ $label }}
                </span>
            </div>
            <div class="card-body">
                @if($activeTemplate)
                    <div style="background:var(--success-bg); border:1px solid var(--success-border); border-radius:var(--radius-sm); padding:13px 15px; margin-bottom:18px;">
                        <div style="font-size:12px; font-weight:700; color:#065f46; margin-bottom:4px;">Template aktif</div>
                        <div style="font-size:13px; color:var(--text); font-weight:600;">{{ $activeTemplate->original_filename }}</div>
                        <div style="font-size:11px; color:var(--text-muted); margin-top:3px;">
                            {{ $activeTemplate->file_size_human }} &bull; diunggah {{ $activeTemplate->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-triangle-exclamation"></i>
                        <div>Belum ada template {{ $label }} aktif.</div>
                    </div>
                @endif

                <form action="{{ route('superadmin.nda-templates.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="template_type" value="{{ $type }}">
                    <div class="form-group">
                        <label class="form-label">File Template {{ $label }} <span class="required">*</span></label>
                        <div id="templateDropZone_{{ $type }}" style="border:1.5px dashed var(--border-dark); border-radius:var(--radius-sm); padding:18px 16px; cursor:pointer; display:flex; align-items:center; gap:12px; background:var(--surface-2);"
                            onclick="document.getElementById('templateInput_{{ $type }}').click()">
                            <i class="fas fa-file-lines" style="color:var(--brand-500); font-size:20px;"></i>
                            <div>
                                <div id="templateLabel_{{ $type }}" style="font-size:13px; font-weight:700; color:var(--text-2);">Pilih file PDF/DOC/DOCX</div>
                                <div style="font-size:11px; color:var(--text-muted);">Maks 5MB. Template baru menggantikan template aktif jenis ini.</div>
                            </div>
                        </div>
                        <input type="file" name="template_file" id="templateInput_{{ $type }}" accept=".pdf,.doc,.docx" style="display:none;" required onchange="showTemplateFile(this, '{{ $type }}')">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">
                        <i class="fas fa-cloud-arrow-up"></i> Simpan Template {{ $label }}
                    </button>
                </form>
            </div>
        </div>
    @endforeach
</div>

@if($errors->any())
    <div class="alert alert-danger" style="margin-top:18px;">
        <i class="fas fa-circle-xmark"></i>
        <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
    </div>
@endif

<div class="card" style="margin-top:20px;">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-clock-rotate-left"></i> Riwayat Template</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Jenis</th>
                    <th>File</th>
                    <th>Status</th>
                    <th>Pengunggah</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $template)
                <tr>
                    <td><span class="badge badge-blue">{{ $template->type_label }}</span></td>
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
                    <td>
                        <a href="{{ route('superadmin.nda-templates.download', $template) }}" class="btn btn-xs btn-outline">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state" style="padding:34px 24px;">
                            <div class="empty-icon"><i class="fas fa-file-contract"></i></div>
                            <h3>Belum ada template dokumen</h3>
                            <p>Unggah template pertama agar Admin OPD dapat mengunduhnya.</p>
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

@push('scripts')
<script>
function showTemplateFile(input, type) {
    const f = input.files[0];
    if (!f) return;
    const size = f.size > 1024*1024 ? (f.size/1024/1024).toFixed(1)+' MB' : (f.size/1024).toFixed(0)+' KB';
    document.getElementById('templateLabel_' + type).textContent = f.name + ' - ' + size;
    document.getElementById('templateDropZone_' + type).style.borderColor = 'var(--success)';
    document.getElementById('templateDropZone_' + type).style.background = 'var(--success-bg)';
}
</script>
@endpush
@endsection
