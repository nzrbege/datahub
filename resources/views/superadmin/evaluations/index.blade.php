@extends('layouts.app')
@section('page-title', 'Evaluasi Pemanfaatan')

@section('content')
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-clipboard-check"></i> Evaluasi Pemanfaatan Data</span>
        <form method="GET" style="display:flex;gap:8px;align-items:center;">
            <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Cari OPD atau dataset..." style="width:260px;">
            <button type="submit" class="btn btn-sm btn-outline"><i class="fas fa-search"></i> Cari</button>
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Waktu Upload</th>
                    <th>Admin OPD</th>
                    <th>Dataset</th>
                    <th>Laporan</th>
                    <th>Catatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($evaluations as $evaluation)
                <tr>
                    <td>
                        <div style="font-size:12.5px;font-weight:700;">{{ $evaluation->submitted_at->format('d/m/Y') }}</div>
                        <div style="font-size:11px;color:var(--text-ghost);">{{ $evaluation->submitted_at->format('H:i') }}</div>
                    </td>
                    <td>
                        <div style="font-weight:700;font-size:13px;">{{ $evaluation->user->name ?? '-' }}</div>
                        <div style="font-size:11px;color:var(--text-muted);">{{ $evaluation->user->instansi ?? '' }}</div>
                    </td>
                    <td>
                        <div style="font-size:13px;font-weight:600;">{{ $evaluation->dataFile->judul ?? '-' }}</div>
                        <div style="font-size:11px;color:var(--text-ghost);">Request #{{ $evaluation->data_request_id }}</div>
                    </td>
                    <td>
                        <div style="font-size:12.5px;font-weight:700;"><i class="fas fa-file-lines" style="color:var(--brand-500);"></i> {{ $evaluation->report_filename }}</div>
                        <div style="font-size:10.5px;color:var(--text-ghost);font-family:'JetBrains Mono',monospace;">{{ substr($evaluation->report_hash, 0, 18) }}...</div>
                    </td>
                    <td style="max-width:260px;">
                        <span style="font-size:12px;color:var(--text-muted);">{{ $evaluation->notes ? \Illuminate\Support\Str::limit($evaluation->notes, 120) : '-' }}</span>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <a href="{{ route('superadmin.evaluations.show', $evaluation) }}" target="_blank" class="btn btn-xs btn-outline">
                                <i class="fas fa-eye"></i> Lihat
                            </a>
                            <a href="{{ route('superadmin.evaluations.download', $evaluation) }}" class="btn btn-xs btn-primary">
                                <i class="fas fa-download"></i> Unduh
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-clipboard-list"></i></div>
                            <h3>Belum ada evaluasi pemanfaatan</h3>
                            <p>Laporan pemanfaatan yang diunggah Admin OPD akan muncul di sini.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($evaluations->hasPages())
        <div style="padding:0 22px 18px;">{{ $evaluations->links() }}</div>
    @endif
</div>
@endsection
