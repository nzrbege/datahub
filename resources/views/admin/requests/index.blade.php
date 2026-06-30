@extends('layouts.app')
@section('page-title', 'Permintaan Data Saya')

@section('content')
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-file-lines"></i> Riwayat Permintaan Akses Data</span>
        <a href="{{ route('admin.requests.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-circle-plus"></i> Ajukan Permintaan Baru
        </a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Dataset</th>
                    <th>Kategori</th>
                    <th>Dokumen</th>
                    <th>Tanggal Diajukan</th>
                    <th>Status</th>
                    <th>Direview</th>
                    <th>Tahap</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                <tr>
                    <td style="color:var(--text-ghost); font-size:12px; font-family:'JetBrains Mono',monospace;">{{ $req->id }}</td>
                    <td>
                        <div style="font-weight:700; font-size:13px;">{{ $req->dataFile->judul ?? '—' }}</div>
                        <div style="font-size:11px; color:var(--text-ghost);">{{ $req->dataFile->tahun_data ?? '' }}</div>
                    </td>
                    <td><span class="badge badge-cat">{{ $req->dataFile->kategori_label ?? '—' }}</span></td>
                    <td style="text-align:center;">
                        <i class="fas fa-file-pdf" style="color:#dc2626; font-size:16px;" title="{{ $req->nda_filename }}"></i>
                    </td>
                    <td>
                        <div style="font-size:12.5px; font-weight:600;">{{ $req->created_at->format('d/m/Y') }}</div>
                        <div style="font-size:11px; color:var(--text-ghost);">{{ $req->created_at->format('H:i') }}</div>
                    </td>
                    <td><span class="badge badge-{{ $req->status }}">{{ $req->status_label }}</span></td>
                    <td>
                        @if($req->reviewed_at)
                            <div style="font-size:12px; font-weight:600;">{{ $req->reviewed_at->format('d/m/Y') }}</div>
                            <div style="font-size:10.5px; color:var(--text-ghost);">{{ $req->reviewer->name ?? '' }}</div>
                        @else
                            <span style="color:var(--text-ghost);">—</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        @if($req->isApproved())
                            @if($req->canDownload())
                                <div style="font-size:13px; font-weight:700; color:var(--success);">
                                    {{ $req->remainingDownloads() }}x
                                </div>
                                <div style="font-size:10.5px; color:var(--text-ghost);">{{ $req->quotaWindowLabel() }}</div>
                            @else
                                <span style="font-size:12px; color:var(--text-ghost);">Habis {{ $req->quotaWindowLabel() }}</span>
                            @endif
                        @else
                            <span style="color:var(--text-ghost);">—</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex; gap:5px;">
                            <a href="{{ route('admin.requests.show', $req) }}" class="btn btn-xs btn-outline" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($req->canDownload())
                            <a href="{{ route('admin.download.show', $req) }}" class="btn btn-xs btn-success" title="Unduh">
                                <i class="fas fa-download"></i>
                            </a>
                            @endif
                            @if($req->isReturned())
                            <a href="{{ route('admin.requests.edit', $req) }}" class="btn btn-xs btn-primary" title="Revisi">
                                <i class="fas fa-file-pen"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-file-circle-plus"></i></div>
                            <h3>Belum ada permintaan data</h3>
                            <p>Ajukan permintaan akses untuk dataset yang Anda butuhkan.</p>
                            <div style="margin-top:18px;">
                                <a href="{{ route('admin.requests.create') }}" class="btn btn-primary">
                                    <i class="fas fa-circle-plus"></i> Ajukan Permintaan
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($requests->hasPages())
    <div style="padding:0 22px;">{{ $requests->links() }}</div>
    @endif
</div>
@endsection
