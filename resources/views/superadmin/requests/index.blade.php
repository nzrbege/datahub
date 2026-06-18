@extends('layouts.app')
@section('page-title', 'Permintaan Akses Data')

@section('content')
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-inbox"></i> Daftar Permintaan Akses</span>
        <form method="GET" style="display:flex; gap:8px; align-items:center;">
            <select name="status" class="form-control" style="width:auto;font-size:12.5px;padding:6px 10px;" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="pending"  {{ request('status')=='pending'  ? 'selected':'' }}>Menunggu</option>
                <option value="approved" {{ request('status')=='approved' ? 'selected':'' }}>Disetujui</option>
                <option value="rejected" {{ request('status')=='rejected' ? 'selected':'' }}>Ditolak</option>
                <option value="revoked"  {{ request('status')=='revoked'  ? 'selected':'' }}>Dicabut</option>
            </select>
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Pemohon &amp; Instansi</th>
                    <th>Dataset Diminta</th>
                    <th>NDA</th>
                    <th>Tanggal Diajukan</th>
                    <th>Status</th>
                    <th>Unduhan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                <tr>
                    <td style="color:var(--text-ghost);font-size:12px;font-family:'JetBrains Mono',monospace;">{{ $req->id }}</td>
                    <td>
                        <div style="font-weight:700;font-size:13px;">{{ $req->user->name ?? '—' }}</div>
                        <div style="font-size:11.5px;color:var(--text-muted);margin-top:2px;">
                            <i class="fas fa-building" style="font-size:10px;"></i> {{ $req->user->instansi ?? '' }}
                        </div>
                    </td>
                    <td>
                        <div style="font-size:13px;font-weight:600;">{{ $req->dataFile->judul ?? '—' }}</div>
                        <div style="font-size:11px;color:var(--text-ghost);margin-top:2px;">
                            {{ $req->dataFile->kategori_label ?? '' }}
                            {{ $req->dataFile->tahun_data ? '· '.$req->dataFile->tahun_data : '' }}
                        </div>
                    </td>
                    <td style="text-align:center;">
                        @if($req->nda_path)
                            <div style="display:flex; gap:5px; justify-content:center;">
                                <a href="{{ route('superadmin.requests.nda', $req) }}" target="_blank" class="btn btn-xs btn-outline" title="Lihat NDA">
                                    <i class="fas fa-file-pdf" style="color:#dc2626;"></i>
                                </a>
                                <a href="{{ route('superadmin.requests.nda', ['dataRequest' => $req, 'mode' => 'download']) }}" class="btn btn-xs btn-ghost" title="Unduh NDA">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        @else
                            <span style="color:var(--text-ghost);font-size:12px;">—</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-size:12.5px;font-weight:600;">{{ $req->created_at->format('d/m/Y') }}</div>
                        <div style="font-size:11px;color:var(--text-ghost);">{{ $req->created_at->format('H:i') }}</div>
                    </td>
                    <td><span class="badge badge-{{ $req->status }}">{{ $req->status_label }}</span></td>
                    <td style="text-align:center;">
                        @if($req->isApproved())
                            <div style="font-size:13px;font-weight:700;color:var(--success);">{{ $req->quotaDownloadCount() }}/{{ $req->max_downloads }}</div>
                            <div style="font-size:11px;color:var(--text-ghost);">{{ $req->quotaWindowLabel() }}</div>
                        @else
                            <span style="color:var(--text-ghost);">—</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('superadmin.requests.show', $req) }}" class="btn btn-xs btn-outline">
                            <i class="fas fa-eye"></i> Review
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                            <h3>Tidak ada permintaan{{ request('status') ? ' — '.request('status') : '' }}</h3>
                            <p>Permintaan akses data dari OPD akan muncul di sini.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($requests->hasPages())
    <div style="padding: 0 22px;">{{ $requests->links() }}</div>
    @endif
</div>
@endsection
