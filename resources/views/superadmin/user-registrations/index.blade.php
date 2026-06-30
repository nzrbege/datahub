@extends('layouts.app')
@section('page-title', 'Permohonan Registrasi User')

@section('content')
@php
    $statusLabels = [
        'pending' => 'Menunggu Verifikasi',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
    ];
@endphp

<div class="stats-grid">
    <a href="{{ route('superadmin.user-registrations.index') }}" class="stat-card blue">
        <div class="stat-top">
            <div>
                <div class="stat-num">{{ $statusCounts->sum() }}</div>
                <div class="stat-label">Semua Permohonan</div>
            </div>
            <div class="stat-icon blue"><i class="fas fa-users"></i></div>
        </div>
    </a>
    <a href="{{ route('superadmin.user-registrations.index', ['status' => 'pending']) }}" class="stat-card orange">
        <div class="stat-top">
            <div>
                <div class="stat-num">{{ $statusCounts['pending'] ?? 0 }}</div>
                <div class="stat-label">Menunggu Verifikasi</div>
            </div>
            <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
        </div>
    </a>
    <a href="{{ route('superadmin.user-registrations.index', ['status' => 'approved']) }}" class="stat-card green">
        <div class="stat-top">
            <div>
                <div class="stat-num">{{ $statusCounts['approved'] ?? 0 }}</div>
                <div class="stat-label">Disetujui</div>
            </div>
            <div class="stat-icon green"><i class="fas fa-circle-check"></i></div>
        </div>
    </a>
    <a href="{{ route('superadmin.user-registrations.index', ['status' => 'rejected']) }}" class="stat-card red">
        <div class="stat-top">
            <div>
                <div class="stat-num">{{ $statusCounts['rejected'] ?? 0 }}</div>
                <div class="stat-label">Ditolak</div>
            </div>
            <div class="stat-icon red"><i class="fas fa-circle-xmark"></i></div>
        </div>
    </a>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-user-check"></i> Verifikasi Permohonan User</span>
        <a href="{{ route('superadmin.user-registrations.index') }}" class="btn btn-sm btn-ghost">
            <i class="fas fa-rotate"></i> Reset
        </a>
    </div>

    <form method="GET" class="filter-bar">
        <div class="form-group" style="min-width:260px;flex:1;">
            <label class="form-label" for="q">Cari Nama / Email / OPD</label>
            <input id="q" type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Ketik kata kunci">
        </div>
        <div class="form-group" style="min-width:220px;">
            <label class="form-label" for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="">Semua Status</option>
                @foreach($statusLabels as $status => $label)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-magnifying-glass"></i> Terapkan
        </button>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Pemohon</th>
                    <th>Instansi / OPD</th>
                    <th>Surat</th>
                    <th>Status</th>
                    <th>Verifikasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $registration)
                    <tr>
                        <td style="color:var(--text-ghost);font-size:12px;font-family:'JetBrains Mono',monospace;">{{ $registration->id }}</td>
                        <td>
                            <div style="font-weight:700;font-size:13px;">{{ $registration->name }}</div>
                            <div style="font-size:11.5px;color:var(--brand-700);font-weight:700;margin-top:2px;">{{ $registration->username ?: 'Username belum ada' }}</div>
                            <div style="font-size:11.5px;color:var(--text-muted);margin-top:2px;">{{ $registration->email }}</div>
                            <div style="font-size:11px;color:var(--text-ghost);margin-top:2px;">{{ $registration->phone }}</div>
                        </td>
                        <td style="font-size:12.5px;font-weight:600;">{{ $registration->instansi }}</td>
                        <td>
                            <div style="font-size:12px;font-weight:600;margin-bottom:6px;">{{ $registration->letter_filename }}</div>
                            <a href="{{ route('superadmin.user-registrations.letter', $registration) }}" target="_blank" class="btn btn-xs btn-outline">
                                <i class="fas fa-file-pdf"></i> Lihat Surat
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-{{ $registration->status }}">{{ $registration->status_label }}</span>
                            <div style="font-size:11px;color:var(--text-ghost);margin-top:5px;">Diajukan {{ $registration->created_at->format('d/m/Y H:i') }}</div>
                        </td>
                        <td>
                            @if($registration->reviewer)
                                <div style="font-size:12.5px;font-weight:700;">{{ $registration->reviewer->name }}</div>
                                <div style="font-size:11px;color:var(--text-ghost);">{{ $registration->reviewed_at?->format('d/m/Y H:i') }}</div>
                                @if($registration->createdUser)
                                    <div style="font-size:11px;color:var(--success);margin-top:4px;">Akun: {{ $registration->createdUser->email }}</div>
                                @endif
                                @if($registration->review_notes)
                                    <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">{{ \Illuminate\Support\Str::limit($registration->review_notes, 90) }}</div>
                                @endif
                            @else
                                <span style="font-size:12px;color:var(--text-ghost);">Belum diverifikasi</span>
                            @endif
                        </td>
                        <td>
                            @if($registration->isPending())
                                <div class="registration-actions">
                                    <button type="button" class="btn btn-xs btn-success" data-modal-target="approve-registration-{{ $registration->id }}">
                                        <i class="fas fa-check"></i> Setujui
                                    </button>
                                    <button type="button" class="btn btn-xs btn-danger" data-modal-target="reject-registration-{{ $registration->id }}">
                                        <i class="fas fa-xmark"></i> Tolak
                                    </button>
                                </div>

                                <div id="approve-registration-{{ $registration->id }}" class="registration-modal" hidden>
                                    <div class="registration-modal__backdrop" data-modal-close></div>
                                    <div class="registration-modal__panel" role="dialog" aria-modal="true" aria-labelledby="approve-title-{{ $registration->id }}">
                                        <div class="registration-modal__header">
                                            <div>
                                                <h3 id="approve-title-{{ $registration->id }}">Setujui Permohonan</h3>
                                                <p>{{ $registration->name }} · {{ $registration->email }}</p>
                                            </div>
                                            <button type="button" class="modal-icon-btn" data-modal-close aria-label="Tutup modal">
                                                <i class="fas fa-xmark"></i>
                                            </button>
                                        </div>

                                        <form action="{{ route('superadmin.user-registrations.approve', $registration) }}" method="POST" class="modal-form">
                                            @csrf
                                            <div class="modal-summary">
                                                <div><span>Username</span><strong>{{ $registration->username ?: 'Akan dibuat dari email' }}</strong></div>
                                                <div><span>Instansi/OPD</span><strong>{{ $registration->instansi }}</strong></div>
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label" for="password_{{ $registration->id }}">Password awal akun</label>
                                                <div class="password-field">
                                                    <input id="password_{{ $registration->id }}" type="password" name="password" class="form-control" required placeholder="Minimal 12 karakter">
                                                    <button type="button" class="password-toggle" data-password-toggle="password_{{ $registration->id }}" aria-label="Tampilkan password">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="password_confirmation_{{ $registration->id }}">Konfirmasi password</label>
                                                <div class="password-field">
                                                    <input id="password_confirmation_{{ $registration->id }}" type="password" name="password_confirmation" class="form-control" required>
                                                    <button type="button" class="password-toggle" data-password-toggle="password_confirmation_{{ $registration->id }}" aria-label="Tampilkan konfirmasi password">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="approve_notes_{{ $registration->id }}">Catatan persetujuan</label>
                                                <textarea id="approve_notes_{{ $registration->id }}" name="review_notes" class="form-control" rows="3" placeholder="Opsional"></textarea>
                                            </div>

                                            <div class="registration-modal__footer">
                                                <button type="button" class="btn btn-sm btn-ghost" data-modal-close>Batal</button>
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i> Setujui & Buat Akun
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div id="reject-registration-{{ $registration->id }}" class="registration-modal" hidden>
                                    <div class="registration-modal__backdrop" data-modal-close></div>
                                    <div class="registration-modal__panel" role="dialog" aria-modal="true" aria-labelledby="reject-title-{{ $registration->id }}">
                                        <div class="registration-modal__header">
                                            <div>
                                                <h3 id="reject-title-{{ $registration->id }}">Tolak Permohonan</h3>
                                                <p>{{ $registration->name }} · {{ $registration->email }}</p>
                                            </div>
                                            <button type="button" class="modal-icon-btn" data-modal-close aria-label="Tutup modal">
                                                <i class="fas fa-xmark"></i>
                                            </button>
                                        </div>

                                        <form action="{{ route('superadmin.user-registrations.reject', $registration) }}" method="POST" class="modal-form">
                                            @csrf
                                            <div class="form-group">
                                                <label class="form-label" for="reject_notes_{{ $registration->id }}">Alasan penolakan</label>
                                                <textarea id="reject_notes_{{ $registration->id }}" name="review_notes" class="form-control" rows="4" placeholder="Tuliskan alasan penolakan dan arahan perbaikan" required></textarea>
                                            </div>

                                            <div class="registration-modal__footer">
                                                <button type="button" class="btn btn-sm btn-ghost" data-modal-close>Batal</button>
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-xmark"></i> Tolak Permohonan
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @else
                                <span style="font-size:12px;color:var(--text-ghost);">Tidak ada aksi lanjutan.</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fas fa-user-check"></i></div>
                                <h3>Belum ada permohonan registrasi</h3>
                                <p>Permohonan user baru dari halaman registrasi akan muncul di sini.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($requests->hasPages())
        <div style="padding:0 22px 18px;">{{ $requests->links() }}</div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .registration-actions {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    .registration-modal[hidden] {
        display: none;
    }

    .registration-modal {
        position: fixed;
        inset: 0;
        z-index: 1000;
        display: grid;
        place-items: center;
        padding: 20px;
    }

    .registration-modal__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, .54);
    }

    .registration-modal__panel {
        position: relative;
        z-index: 1;
        width: min(520px, 100%);
        max-height: calc(100vh - 40px);
        overflow: auto;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 8px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .28);
    }

    .registration-modal__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--border);
    }

    .registration-modal__header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
        color: var(--text);
    }

    .registration-modal__header p {
        margin: 4px 0 0;
        font-size: 12px;
        color: var(--text-muted);
    }

    .modal-icon-btn,
    .password-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border);
        background: var(--surface-2);
        color: var(--text-muted);
        cursor: pointer;
    }

    .modal-icon-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
    }

    .modal-form {
        display: grid;
        gap: 14px;
        padding: 20px;
    }

    .modal-summary {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        padding: 12px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--surface-2);
    }

    .modal-summary span {
        display: block;
        font-size: 10.5px;
        font-weight: 700;
        color: var(--text-ghost);
        text-transform: uppercase;
    }

    .modal-summary strong {
        display: block;
        margin-top: 4px;
        font-size: 12px;
        color: var(--text);
    }

    .password-field {
        position: relative;
    }

    .password-field .form-control {
        padding-right: 42px;
    }

    .password-toggle {
        position: absolute;
        top: 50%;
        right: 8px;
        width: 30px;
        height: 30px;
        border-radius: 7px;
        transform: translateY(-50%);
    }

    .registration-modal__footer {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding-top: 4px;
    }

    @media (max-width: 560px) {
        .modal-summary {
            grid-template-columns: 1fr;
        }

        .registration-modal__footer {
            flex-direction: column-reverse;
        }

        .registration-modal__footer .btn {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('click', function (event) {
        const opener = event.target.closest('[data-modal-target]');
        if (opener) {
            const modal = document.getElementById(opener.dataset.modalTarget);
            if (modal) {
                modal.hidden = false;
                document.body.style.overflow = 'hidden';
                const firstInput = modal.querySelector('input, textarea, button');
                if (firstInput) firstInput.focus();
            }
            return;
        }

        const closer = event.target.closest('[data-modal-close]');
        if (closer) {
            const modal = closer.closest('.registration-modal');
            if (modal) {
                modal.hidden = true;
                document.body.style.overflow = '';
            }
            return;
        }

        const passwordToggle = event.target.closest('[data-password-toggle]');
        if (passwordToggle) {
            const input = document.getElementById(passwordToggle.dataset.passwordToggle);
            const icon = passwordToggle.querySelector('i');
            if (!input) return;

            const shouldShow = input.type === 'password';
            input.type = shouldShow ? 'text' : 'password';
            passwordToggle.setAttribute('aria-label', shouldShow ? 'Sembunyikan password' : 'Tampilkan password');
            if (icon) {
                icon.classList.toggle('fa-eye', !shouldShow);
                icon.classList.toggle('fa-eye-slash', shouldShow);
            }
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;

        document.querySelectorAll('.registration-modal:not([hidden])').forEach(function (modal) {
            modal.hidden = true;
        });
        document.body.style.overflow = '';
    });
</script>
@endpush
