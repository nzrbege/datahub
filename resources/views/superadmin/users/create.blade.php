@extends('layouts.app')
@section('page-title', 'Tambah Pengguna')

@push('styles')
<style>
    .password-wrap { position: relative; }
    .password-wrap .form-control { padding-right: 44px; }
    .password-toggle {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        width: 32px;
        height: 32px;
        border: none;
        border-radius: 8px;
        background: transparent;
        color: var(--text-muted);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .password-toggle:hover { background: #f1f5f9; color: var(--text); }
</style>
@endpush

@section('content')
<div style="margin-bottom:20px;">
    <a href="{{ route('superadmin.users.index') }}" class="btn btn-ghost btn-sm">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Pengguna
    </a>
</div>

<div class="pdp-notice">
    <i class="fas fa-shield-halved"></i>
    <div><strong>UU PDP Pasal 50 — Prinsip Need-to-Know:</strong> Setiap penambahan pengguna wajib berdasarkan kebutuhan nyata. Berikan akses seminimal mungkin sesuai tugas dan tanggung jawab.</div>
</div>

<div class="card" style="max-width:760px;">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-user-plus"></i> Data Pengguna Baru</span>
    </div>
    <div class="card-body">
        <form action="{{ route('superadmin.users.store') }}" method="POST">
            @csrf
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0 20px;">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                        value="{{ old('name') }}" required placeholder="Nama lengkap pengguna">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Username <span class="required">*</span></label>
                    <input type="text" name="username" class="form-control {{ $errors->has('username') ? 'is-invalid' : '' }}"
                        value="{{ old('username') }}" required placeholder="username_opd">
                    @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Huruf kecil, angka, dash, atau underscore.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Alamat Email <span class="required">*</span></label>
                    <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                        value="{{ old('email') }}" required placeholder="nama@instansi.go.id">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Instansi / OPD <span class="required">*</span></label>
                    <input type="text" name="instansi" class="form-control {{ $errors->has('instansi') ? 'is-invalid' : '' }}"
                        value="{{ old('instansi') }}" required placeholder="Dinas / Badan / Kantor">
                    @error('instansi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Jabatan <span class="required">*</span></label>
                    <input type="text" name="jabatan" class="form-control {{ $errors->has('jabatan') ? 'is-invalid' : '' }}"
                        value="{{ old('jabatan') }}" required placeholder="Kepala Sub Bagian / dll">
                    @error('jabatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Nomor Telepon</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+62812xxxxxxxx">
                </div>
                <div class="form-group">
                    <label class="form-label">Role / Hak Akses <span class="required">*</span></label>
                    <select name="role" class="form-control" required>
                        <option value="admin" {{ old('role')=='admin' ? 'selected':'' }}>Admin OPD</option>
                        <option value="super_admin" {{ old('role')=='super_admin' ? 'selected':'' }}>Super Admin</option>
                    </select>
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <div style="border-top:1px solid var(--border); padding-top:18px; margin-top:4px;">
                        <div style="font-size:12.5px; font-weight:700; color:var(--text-2); margin-bottom:14px;">
                            <i class="fas fa-key" style="color:var(--brand-500);"></i> Kata Sandi
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Kata Sandi <span class="required">*</span></label>
                    <div class="password-wrap">
                        <input type="password" name="password" id="pwInput"
                            class="form-control password-field {{ $errors->has('password') ? 'is-invalid' : '' }}" required>
                        <button type="button" class="password-toggle" aria-label="Lihat kata sandi" title="Lihat kata sandi">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div id="pwStrength" style="margin-top:8px; display:none;">
                        <div style="height:4px; background:var(--border); border-radius:2px; margin-bottom:5px;">
                            <div id="pwBar" style="height:100%; border-radius:2px; transition:width .3s, background .3s; width:0;"></div>
                        </div>
                        <span id="pwLabel" style="font-size:11.5px; font-weight:600;"></span>
                    </div>
                    <div class="form-text">Min. 12 karakter: huruf besar, kecil, angka &amp; karakter khusus (@$!%*?&#)</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Konfirmasi Kata Sandi <span class="required">*</span></label>
                    <div class="password-wrap">
                        <input type="password" name="password_confirmation" class="form-control password-field" required>
                        <button type="button" class="password-toggle" aria-label="Lihat kata sandi" title="Lihat kata sandi">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div style="display:flex; gap:10px; padding-top:16px; border-top:1px solid var(--border); margin-top:4px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Tambah Pengguna
                </button>
                <a href="{{ route('superadmin.users.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const pw = document.getElementById('pwInput');
const bar = document.getElementById('pwBar');
const lbl = document.getElementById('pwLabel');
const wrap = document.getElementById('pwStrength');
pw.addEventListener('input', function() {
    const v = this.value;
    if (!v) { wrap.style.display='none'; return; }
    wrap.style.display='block';
    let s = 0;
    if (v.length >= 12) s++;
    if (/[A-Z]/.test(v)) s++;
    if (/[a-z]/.test(v)) s++;
    if (/\d/.test(v)) s++;
    if (/[@$!%*?&#]/.test(v)) s++;
    const clrs = ['#dc2626','#f59e0b','#eab308','#10b981','#059669'];
    const lbls = ['Sangat Lemah','Lemah','Cukup','Kuat','Sangat Kuat'];
    bar.style.width = (s/5*100)+'%';
    bar.style.background = clrs[s-1]||'#dc2626';
    lbl.textContent = lbls[s-1]||'Sangat Lemah';
    lbl.style.color = clrs[s-1]||'#dc2626';
});

document.querySelectorAll('.password-toggle').forEach(function(button) {
    button.addEventListener('click', function() {
        const input = this.closest('.password-wrap').querySelector('.password-field');
        const icon = this.querySelector('i');
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        icon.classList.toggle('fa-eye', !isHidden);
        icon.classList.toggle('fa-eye-slash', isHidden);
        this.setAttribute('aria-label', isHidden ? 'Sembunyikan kata sandi' : 'Lihat kata sandi');
        this.setAttribute('title', isHidden ? 'Sembunyikan kata sandi' : 'Lihat kata sandi');
    });
});
</script>
@endpush
@endsection
