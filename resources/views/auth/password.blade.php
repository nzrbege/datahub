@extends('layouts.app')
@section('page-title', 'Ubah / Reset Password')

@push('styles')
<style>
    .password-page {
        max-width: 760px;
    }
    .password-wrap {
        position: relative;
    }
    .password-wrap .form-control {
        padding-right: 44px;
    }
    .password-toggle {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        width: 30px;
        height: 30px;
        border: 0;
        border-radius: 6px;
        background: transparent;
        color: var(--text-muted);
        cursor: pointer;
    }
    .password-toggle:hover {
        background: var(--surface-3);
        color: var(--text);
    }
    .password-rules {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 8px;
        margin-top: 12px;
    }
    .password-rule {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 9px 11px;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        background: var(--surface-2);
        font-size: 12px;
        color: var(--text-muted);
    }
    .password-rule i {
        color: var(--brand-500);
    }
</style>
@endpush

@section('content')
<div class="password-page">
    <div class="pdp-notice">
        <i class="fas fa-key"></i>
        <div><strong>Keamanan Akun:</strong> Gunakan password yang kuat dan jangan bagikan kepada siapa pun. Setelah password diperbarui, gunakan password baru pada login berikutnya.</div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-lock"></i> Ubah / Reset Password Saya</span>
        </div>
        <div class="card-body">
            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">Password Lama <span class="required">*</span></label>
                    <div class="password-wrap">
                        <input type="password" name="current_password" class="form-control password-field" autocomplete="current-password" required>
                        <button type="button" class="password-toggle" aria-label="Lihat password" title="Lihat password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Password Baru <span class="required">*</span></label>
                    <div class="password-wrap">
                        <input type="password" name="password" class="form-control password-field" autocomplete="new-password" required>
                        <button type="button" class="password-toggle" aria-label="Lihat password" title="Lihat password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror

                    <div class="password-rules">
                        <div class="password-rule"><i class="fas fa-check"></i> Minimal 12 karakter</div>
                        <div class="password-rule"><i class="fas fa-check"></i> Huruf besar dan kecil</div>
                        <div class="password-rule"><i class="fas fa-check"></i> Angka</div>
                        <div class="password-rule"><i class="fas fa-check"></i> Karakter khusus</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Konfirmasi Password Baru <span class="required">*</span></label>
                    <div class="password-wrap">
                        <input type="password" name="password_confirmation" class="form-control password-field" autocomplete="new-password" required>
                        <button type="button" class="password-toggle" aria-label="Lihat password" title="Lihat password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-floppy-disk"></i> Simpan Password Baru
                    </button>
                    <a href="{{ auth()->user()->isSuperAdmin() ? route('superadmin.dashboard') : route('admin.dashboard') }}" class="btn btn-ghost">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.password-toggle').forEach(function(button) {
    button.addEventListener('click', function() {
        const input = this.closest('.password-wrap').querySelector('.password-field');
        const icon = this.querySelector('i');
        const isHidden = input.type === 'password';

        input.type = isHidden ? 'text' : 'password';
        icon.classList.toggle('fa-eye', !isHidden);
        icon.classList.toggle('fa-eye-slash', isHidden);
    });
});
</script>
@endpush
