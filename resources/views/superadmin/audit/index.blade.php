@extends('layouts.app')
@section('page-title', 'Log Aktivitas')

@section('content')
<div class="pdp-notice">
    <i class="fas fa-shield-halved"></i>
    <div><strong>UU PDP Pasal 47 - Log Wajib:</strong> Catatan ini merupakan log permanen seluruh aktivitas pemrosesan data pribadi. Log tidak dapat dihapus secara manual dan disimpan untuk keperluan audit kepatuhan.</div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-shield-halved"></i> Log Aktivitas Pemrosesan Data</span>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('superadmin.audit.export', request()->query()) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-file-export"></i> Export CSV
            </a>
            <a href="{{ route('superadmin.audit.downloads') }}" class="btn btn-sm btn-outline">
                <i class="fas fa-arrow-down-to-line"></i> Log Unduhan
            </a>
        </div>
    </div>

    <form method="GET">
        <div class="filter-bar">
            <div class="form-group">
                <label class="form-label">Aksi</label>
                <select name="action" class="form-control" style="width:auto;">
                    <option value="">Semua Aksi</option>
                    @foreach($actionOptions as $act => $label)
                    <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control" style="width:auto;" value="{{ request('date_from') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Sampai</label>
                <input type="date" name="date_to" class="form-control" style="width:auto;" value="{{ request('date_to') }}">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-filter"></i> Filter
            </button>
            <a href="{{ route('superadmin.audit.index') }}" class="btn btn-ghost btn-sm">
                <i class="fas fa-rotate-left"></i> Reset
            </a>
        </div>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Nama &amp; Username</th>
                    <th>Instansi/OPD</th>
                    <th>Aksi yang Dilakukan</th>
                    <th>Data Terkait</th>
                    <th>Rincian</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td>
                        <div style="font-size:12.5px;font-weight:600;">{{ $log->occurred_at->format('d/m/Y') }}</div>
                        <div style="font-size:11px;color:var(--text-ghost);font-family:'JetBrains Mono',monospace;">{{ $log->occurred_at->format('H:i:s') }}</div>
                    </td>
                    <td>
                        <div style="font-weight:700;font-size:13px;">{{ $log->user->name ?? 'Sistem' }}</div>
                        <div style="font-size:11px;color:var(--text-muted);">
                            Username: <span style="font-family:'JetBrains Mono',monospace;">{{ $log->actor_username }}</span>
                        </div>
                        <div style="font-size:10.5px;color:var(--text-ghost);">ID pengguna: {{ $log->user_id ?? '-' }}</div>
                    </td>
                    <td>
                        <div style="font-size:12.5px;color:var(--text-muted);max-width:180px;">{{ $log->actor_institution }}</div>
                    </td>
                    <td>
                        <span class="action-badge {{ $log->action_class }}">{{ $log->action_label }}</span>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:5px;max-width:210px;">{{ $log->action_description }}</div>
                    </td>
                    <td style="font-size:12px;color:var(--text-muted);">
                        {{ $log->resource_label }}
                    </td>
                    <td style="font-size:11.5px;color:var(--text-muted);min-width:220px;max-width:320px;">
                        <div>{{ $log->context_summary }}</div>
                        @if($log->dasar_hukum)
                            <div style="margin-top:5px;"><strong>Dasar hukum:</strong> {{ $log->dasar_hukum }}</div>
                        @endif
                        @if($log->tujuan)
                            <div style="margin-top:3px;"><strong>Tujuan:</strong> {{ $log->tujuan }}</div>
                        @endif
                    </td>
                    <td style="font-family:'JetBrains Mono',monospace;font-size:12px;color:var(--text-muted);">{{ $log->ip_address }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-shield-halved"></i></div>
                            <h3>Tidak ada log aktivitas</h3>
                            <p>Log akan muncul seiring penggunaan sistem.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
    <div style="padding: 0 22px;">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
