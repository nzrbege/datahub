@extends('layouts.app')
@section('page-title', 'Detail Dataset')

@section('content')
<div style="margin-bottom:20px;">
    <a href="{{ route('superadmin.files.index') }}" class="btn btn-ghost btn-sm">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Dataset
    </a>
</div>

<div class="detail-grid">
    <!-- Left: Info + Permissions -->
    <div>
        <!-- Info Dataset -->
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-database"></i> {{ $dataFile->judul }}</span>
                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <a href="{{ route('superadmin.files.download', $dataFile) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-download"></i> Unduh File
                    </a>
                    <span class="badge {{ $dataFile->is_active ? 'badge-active' : 'badge-inactive' }}">
                        {{ $dataFile->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="info-grid" style="margin-bottom:18px;">
                    <div class="info-row">
                        <div class="info-label">Nama File Asli</div>
                        <div class="info-value" style="font-family:'JetBrains Mono',monospace; font-size:12px;">
                            <i class="fas fa-file" style="color:var(--brand-500);"></i> {{ $dataFile->original_filename }}
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Ukuran File</div>
                        <div class="info-value">{{ $dataFile->file_size_human }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Jenis Dataset</div>
                        <div class="info-value">{{ $dataFile->kategori_label }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Periode Data</div>
                        <div class="info-value">{{ $dataFile->tahun_data ?? '—' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Penyimpanan</div>
                        <div class="info-value">
                            <i class="fas fa-shield-halved" style="color:var(--success);"></i> Storage privat; file diproteksi sebelum upload
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Diunggah oleh</div>
                        <div class="info-value">{{ $dataFile->uploader->name ?? '—' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Tanggal Upload</div>
                        <div class="info-value">{{ $dataFile->created_at->format('d F Y, H:i') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Hash Integritas (SHA-256)</div>
                        <div class="info-value" style="font-family:'JetBrains Mono',monospace; font-size:11px; color:var(--text-muted); word-break:break-all;">
                            {{ substr($dataFile->file_hash, 0, 40) }}…
                        </div>
                    </div>
                </div>
                @if($dataFile->deskripsi)
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Deskripsi Dataset</label>
                    <div class="text-block">{{ $dataFile->deskripsi }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Kelola Izin Akses -->
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-user-shield"></i> Kelola Izin Akses OPD</span>
            </div>
            <div class="card-body">
                <div class="pdp-notice" style="margin-bottom:16px;">
                    <i class="fas fa-circle-info"></i>
                    <div>Hanya OPD yang dicentang yang dapat melihat dan mengunduh dataset ini. Perubahan berlaku segera setelah disimpan.</div>
                </div>

                <form action="{{ route('superadmin.files.permissions', $dataFile) }}" method="POST">
                    @csrf @method('PUT')
                    @if($allAdmins->isEmpty())
                        <div class="alert alert-warning">
                            <i class="fas fa-triangle-exclamation"></i>
                            <div>Belum ada admin OPD yang terdaftar.</div>
                        </div>
                    @else
                        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:8px; margin-bottom:18px;">
                            @foreach($allAdmins as $admin)
                            @php $isAllowed = in_array($admin->id, $allowedIds); @endphp
                            <label style="display:flex; align-items:flex-start; gap:10px; padding:12px 13px; border:1.5px solid {{ $isAllowed ? 'var(--brand-400)' : 'var(--border)' }}; border-radius:var(--radius-sm); cursor:pointer; font-size:12.5px; background:{{ $isAllowed ? 'var(--brand-50)' : 'var(--surface)' }}; transition:all .15s;"
                                onmouseover="this.style.borderColor='var(--brand-400)'"
                                onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='var(--border)'">
                                <input type="checkbox" name="admin_ids[]" value="{{ $admin->id }}"
                                    {{ $isAllowed ? 'checked' : '' }}
                                    style="margin-top:2px; accent-color:var(--brand-500); cursor:pointer; width:14px; height:14px;">
                                <div>
                                    <div style="font-weight:700; color:var(--text);">
                                        {{ $admin->name }}
                                        @if(!$admin->is_active)
                                            <span class="badge badge-inactive" style="font-size:9px; padding:1px 6px;">Nonaktif</span>
                                        @endif
                                    </div>
                                    <div style="font-size:11px; color:var(--text-muted); margin-top:1px;">{{ $admin->instansi }}</div>
                                    <div style="font-size:10.5px; color:var(--text-ghost);">{{ $admin->jabatan }}</div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-floppy-disk"></i> Simpan Perubahan Izin
                        </button>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Right: Stats + Requests -->
    <div>
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-chart-bar"></i> Statistik</span>
            </div>
            <div class="card-body" style="padding:14px 20px;">
                <div class="quick-item">
                    <span style="font-size:13px; color:var(--text-muted);">Total Permintaan</span>
                    <strong style="font-size:15px;">{{ $dataFile->dataRequests->count() }}</strong>
                </div>
                <div class="quick-item">
                    <span style="font-size:13px; color:var(--text-muted);">Disetujui</span>
                    <strong style="color:var(--success);">{{ $dataFile->dataRequests->where('status','bast_approved')->count() }}</strong>
                </div>
                <div class="quick-item">
                    <span style="font-size:13px; color:var(--text-muted);">Pending</span>
                    <strong style="color:var(--warning);">{{ $dataFile->dataRequests->where('status','pending')->count() }}</strong>
                </div>
                <div class="quick-item" style="border:none;">
                    <span style="font-size:13px; color:var(--text-muted);">OPD Diizinkan</span>
                    <strong style="color:var(--brand-600);">{{ count($allowedIds) }}</strong>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-file-lines"></i> Permintaan Terbaru</span>
                <a href="{{ route('superadmin.requests.index') }}" class="btn btn-xs btn-outline">Semua</a>
            </div>
            <div style="padding: 0 22px 10px;">
                @forelse($dataFile->dataRequests->take(5) as $req)
                <div class="quick-item">
                    <div class="quick-item-info">
                        <strong>{{ $req->user->name ?? '—' }}</strong>
                        <span>{{ $req->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <span class="badge badge-{{ $req->status }}">{{ $req->status_label }}</span>
                </div>
                @empty
                <div class="empty-state" style="padding:28px 0;">
                    <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                    <p style="font-size:13px;">Belum ada permintaan.</p>
                </div>
                @endforelse
            </div>
        </div>

        <div class="pdp-notice">
            <i class="fas fa-shield-halved"></i>
            <div><strong>UU PDP Pasal 47:</strong> Setiap akses ke dataset ini tercatat secara permanen dalam audit log dan tidak dapat dihapus secara manual.</div>
        </div>
    </div>
</div>
@endsection
