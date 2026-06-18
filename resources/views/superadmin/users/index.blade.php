@extends('layouts.app')
@section('page-title', 'Pengguna OPD')

@section('content')
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-users-gear"></i> Daftar Pengguna Sistem</span>
        <a href="{{ route('superadmin.users.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-user-plus"></i> Tambah Pengguna
        </a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama &amp; Jabatan</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Instansi / OPD</th>
                    <th>Role</th>
                    <th>Login Terakhir</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr style="{{ $user->trashed() ? 'opacity:.45;' : '' }}">
                    <td style="color:var(--text-ghost);font-size:12px;font-family:'JetBrains Mono',monospace;">{{ $user->id }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--brand-500),var(--brand-300));display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0;">
                                {{ strtoupper(substr($user->name,0,1)) }}
                            </div>
                            <div>
                                <div style="font-weight:700;font-size:13px;">
                                    {{ $user->name }}
                                    @if($user->trashed())
                                        <span class="badge badge-inactive" style="font-size:10px;">Dihapus</span>
                                    @endif
                                    @if($user->isLocked())
                                        <span class="badge" style="background:#fce7f3;color:#9d174d;font-size:10px;">Terkunci</span>
                                    @endif
                                </div>
                                <div style="font-size:11.5px;color:var(--text-muted);">{{ $user->jabatan ?? '—' }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:12.5px;font-family:'JetBrains Mono',monospace;color:var(--text-2);">{{ $user->username ?? '-' }}</td>
                    <td style="font-size:12.5px;color:var(--text-muted);">{{ $user->email }}</td>
                    <td style="font-size:12.5px;font-weight:600;">{{ $user->instansi ?? '—' }}</td>
                    <td>
                        @foreach($user->getRoleNames() as $role)
                            <span class="badge {{ $role === 'super_admin' ? 'badge-sa' : 'badge-admin' }}">
                                {{ $role === 'super_admin' ? 'Super Admin' : 'Admin OPD' }}
                            </span>
                        @endforeach
                    </td>
                    <td>
                        @if($user->last_login_at)
                            <div style="font-size:12.5px;font-weight:600;">{{ $user->last_login_at->format('d/m/Y H:i') }}</div>
                            <div style="font-size:11px;color:var(--text-ghost);font-family:'JetBrains Mono',monospace;">{{ $user->last_login_ip }}</div>
                        @else
                            <span style="color:var(--text-ghost);font-size:12px;">Belum pernah</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $user->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td>
                        @if(!$user->trashed())
                        <div style="display:flex;gap:5px;">
                            <a href="{{ route('superadmin.users.edit', $user) }}" class="btn btn-xs btn-outline" title="Edit">
                                <i class="fas fa-pen-to-square"></i>
                            </a>
                            @if($user->id !== auth()->id())
                            <form action="{{ route('superadmin.users.toggle-active', $user) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="btn btn-xs {{ $user->is_active ? 'btn-warning' : 'btn-success' }}"
                                    title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-users"></i></div>
                            <h3>Belum ada pengguna</h3>
                            <p>Tambahkan akun untuk OPD yang akan menggunakan sistem ini.</p>
                            <div style="margin-top:18px;">
                                <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary">
                                    <i class="fas fa-user-plus"></i> Tambah Pengguna
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div style="padding: 0 22px;">{{ $users->links() }}</div>
    @endif
</div>

<div class="pdp-notice">
    <i class="fas fa-shield-halved"></i>
    <div><strong>UU PDP Pasal 50–51:</strong> Setiap perubahan data pengguna, pemberian, dan pencabutan akses tercatat dalam audit log. Hapus atau nonaktifkan akun pengguna yang sudah tidak aktif untuk meminimalisir risiko kebocoran data.</div>
</div>
@endsection
