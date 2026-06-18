@extends('layouts.app')
@section('page-title', 'Kelola Dataset')

@section('content')
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-database"></i> Daftar Dataset</span>
        <a href="{{ route('superadmin.files.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-circle-plus"></i> Unggah Dataset Baru
        </a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Judul Dataset</th>
                    <th>Kategori</th>
                    <th>Periode</th>
                    <th>Ukuran</th>
                    <th>Akses OPD</th>
                    <th>Diunggah</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($files as $file)
                <tr>
                    <td style="color:var(--text-ghost); font-size:12px; font-family:'JetBrains Mono',monospace;">{{ $file->id }}</td>
                    <td>
                        <div style="display:flex; align-items:flex-start; gap:10px;">
                            <div style="width:32px;height:32px;border-radius:8px;background:var(--brand-100);color:var(--brand-600);display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;">
                                <i class="fas fa-table"></i>
                            </div>
                            <div>
                                <div style="font-weight:700;font-size:13px;">{{ $file->judul }}</div>
                                <div style="font-size:11px;color:var(--text-ghost);font-family:'JetBrains Mono',monospace;margin-top:2px;">
                                    <i class="fas fa-lock" style="color:var(--success);font-size:9px;"></i> {{ $file->original_filename }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge badge-cat">{{ $file->kategori_label }}</span></td>
                    <td style="font-size:13px;">{{ $file->tahun_data ?? '—' }}</td>
                    <td style="font-size:12px; font-family:'JetBrains Mono',monospace; color:var(--text-muted);">{{ $file->file_size_human }}</td>
                    <td>
                        @if($file->allowed_users_count > 0)
                            <span style="font-size:13px;font-weight:700;color:var(--success);">{{ $file->allowed_users_count }} OPD</span>
                        @else
                            <span style="color:var(--text-ghost);font-size:12px;">Hanya Super Admin</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-size:12.5px;font-weight:600;">{{ $file->uploader->name ?? '—' }}</div>
                        <div style="font-size:11px;color:var(--text-ghost);">{{ $file->created_at->format('d/m/Y') }}</div>
                    </td>
                    <td>
                        <span class="badge {{ $file->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $file->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td>
                        <div style="display:flex; gap:6px;">
                            <a href="{{ route('superadmin.files.show', $file) }}" class="btn btn-xs btn-outline" title="Detail & Izin Akses">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form action="{{ route('superadmin.files.destroy', $file) }}" method="POST"
                                onsubmit="return confirm('Hapus dataset ini? Data tidak dapat dipulihkan.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-danger" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-database"></i></div>
                            <h3>Belum ada dataset</h3>
                            <p>Mulai dengan mengunggah dataset pertama untuk dibagikan ke OPD.</p>
                            <div style="margin-top:18px;">
                                <a href="{{ route('superadmin.files.create') }}" class="btn btn-primary">
                                    <i class="fas fa-circle-plus"></i> Unggah Dataset
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($files->hasPages())
    <div style="padding: 0 22px;">{{ $files->links() }}</div>
    @endif
</div>

<div class="pdp-notice">
    <i class="fas fa-shield-halved"></i>
    <div><strong>Keamanan Penyimpanan:</strong> Semua file tersimpan dalam format terenkripsi AES-256 di storage privat dan tidak dapat diakses langsung via URL. Setiap akses dan unduhan tercatat dalam audit log sesuai UU PDP Pasal 47.</div>
</div>
@endsection
