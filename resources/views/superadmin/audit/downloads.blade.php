@extends('layouts.app')
@section('page-title', 'Log Unduhan')

@section('content')
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-arrow-down-to-line"></i> Log Unduhan File Dataset</span>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('superadmin.audit.downloads.export') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-file-export"></i> Export CSV
            </a>
            <a href="{{ route('superadmin.audit.index') }}" class="btn btn-sm btn-outline">
                <i class="fas fa-shield-halved"></i> Log Aktivitas
            </a>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Pengguna &amp; Instansi</th>
                    <th>Dataset</th>
                    <th>IP Address</th>
                    <th>Captcha</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td>
                        <div style="font-size:12.5px; font-weight:600;">{{ $log->downloaded_at->format('d/m/Y') }}</div>
                        <div style="font-size:11px; color:var(--text-ghost); font-family:'JetBrains Mono',monospace;">{{ $log->downloaded_at->format('H:i:s') }}</div>
                    </td>
                    <td>
                        <div style="font-weight:700; font-size:13px;">{{ $log->user->name ?? '—' }}</div>
                        <div style="font-size:11px; color:var(--text-muted);">{{ $log->user->instansi ?? '' }}</div>
                    </td>
                    <td>
                        <div style="font-size:13px; font-weight:600;">{{ $log->dataFile->judul ?? '—' }}</div>
                        <div style="font-size:11px; color:var(--text-ghost); font-family:'JetBrains Mono',monospace;">{{ $log->dataFile->original_filename ?? '' }}</div>
                    </td>
                    <td style="font-family:'JetBrains Mono',monospace; font-size:12px; color:var(--text-muted);">{{ $log->ip_address }}</td>
                    <td style="text-align:center;">
                        @if($log->captcha_passed)
                            <i class="fas fa-circle-check" style="color:var(--success); font-size:16px;"></i>
                        @else
                            <i class="fas fa-circle-xmark" style="color:var(--danger); font-size:16px;"></i>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $log->status === 'success' ? 'badge-approved' : ($log->status === 'blocked' ? 'badge-rejected' : 'badge-revoked') }}">
                            {{ $log->status }}
                        </span>
                    </td>
                    <td style="font-size:12px; color:var(--text-muted);">{{ $log->keterangan ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-arrow-down-to-line"></i></div>
                            <h3>Belum ada log unduhan</h3>
                            <p>Log unduhan akan muncul ketika pengguna mengunduh dataset.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div style="padding:0 22px;">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
