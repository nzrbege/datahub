@extends('layouts.app')
@section('page-title', 'Edit Pengguna')

@section('content')
<div style="margin-bottom:20px;">
    <a href="{{ route('superadmin.users.index') }}" class="btn btn-ghost btn-sm">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Pengguna
    </a>
</div>

<div style="max-width:760px; display:flex; flex-direction:column; gap:16px;">

    <!-- Edit Form -->
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-pen-to-square"></i> Edit: {{ $user->name }}</span>
            <span class="badge {{ $user->is_active ? 'badge-active' : 'badge-inactive' }}">
                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
        </div>
        <div class="card-body">
            <form action="{{ route('superadmin.users.update', $user) }}" method="POST">
                @csrf @method('PUT')
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0 20px;">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Username <span class="required">*</span></label>
                        <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" required>
                        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="{{ $user->email }}" disabled
                            style="background:var(--surface-3); color:var(--text-muted); cursor:not-allowed;">
                        <div class="form-text">Email tidak dapat diubah.</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Instansi / OPD <span class="required">*</span></label>
                        <input type="text" name="instansi" class="form-control" value="{{ old('instansi', $user->instansi) }}" required>
                        @error('instansi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jabatan <span class="required">*</span></label>
                        <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan', $user->jabatan) }}" required>
                        @error('jabatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nomor Telepon</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role / Hak Akses <span class="required">*</span></label>
                        <select name="role" class="form-control" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                            <option value="admin"       {{ $user->hasRole('admin')       ? 'selected':'' }}>Admin OPD</option>
                            <option value="super_admin" {{ $user->hasRole('super_admin') ? 'selected':'' }}>Super Admin</option>
                        </select>
                        @if($user->id === auth()->id())
                            <input type="hidden" name="role" value="{{ $user->getRoleNames()->first() }}">
                            <div class="form-text" style="color:var(--warning);">Role Anda sendiri tidak dapat diubah.</div>
                        @endif
                    </div>
                </div>
                <div style="display:flex; gap:10px; padding-top:16px; border-top:1px solid var(--border); margin-top:4px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-floppy-disk"></i> Simpan Perubahan
                    </button>
                    <a href="{{ route('superadmin.users.index') }}" class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Reset Password -->
    <div class="card" style="border-top:3px solid var(--warning);">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-key"></i> Reset Kata Sandi</span>
        </div>
        <div class="card-body">
            <form action="{{ route('superadmin.users.reset-password', $user) }}" method="POST">
                @csrf
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0 20px;">
                    <div class="form-group">
                        <label class="form-label">Kata Sandi Baru <span class="required">*</span></label>
                        <input type="password" name="password" class="form-control" required>
                        <div class="form-text">Min. 12 karakter, kombinasi huruf besar/kecil, angka &amp; karakter khusus.</div>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Konfirmasi Kata Sandi Baru <span class="required">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-warning"
                    onclick="return confirm('Reset kata sandi pengguna ini?')">
                    <i class="fas fa-rotate"></i> Reset Kata Sandi
                </button>
            </form>
        </div>
    </div>

    <!-- Info Akun -->
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-circle-info"></i> Info Akun</span>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Username</div>
                    <div class="info-value" style="font-family:'JetBrains Mono',monospace;">{{ $user->username }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Dibuat pada</div>
                    <div class="info-value">{{ $user->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Login terakhir</div>
                    <div class="info-value">{{ $user->last_login_at?->format('d/m/Y H:i') ?? '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">IP terakhir</div>
                    <div class="info-value" style="font-family:'JetBrains Mono',monospace;">{{ $user->last_login_ip ?? '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Gagal login</div>
                    <div class="info-value">{{ $user->failed_login_attempts }}×</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Terkunci sampai</div>
                    <div class="info-value">{{ $user->locked_until?->format('d/m/Y H:i') ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
