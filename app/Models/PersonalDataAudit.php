<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalDataAudit extends Model
{
    protected $table = 'personal_data_audit';

    public const ACTION_LABELS = [
        'login'             => 'Masuk ke sistem',
        'logout'            => 'Keluar dari sistem',
        'login_failed'      => 'Gagal masuk',
        'file_upload'       => 'Mengunggah dataset',
        'file_download'     => 'Mengunduh dataset',
        'file_view'         => 'Melihat detail dataset',
        'file_delete'       => 'Menghapus dataset',
        'request_create'    => 'Mengajukan permintaan data',
        'request_revise'    => 'Merevisi permintaan data',
        'request_approve'   => 'Menyetujui permintaan data',
        'request_reject'    => 'Menolak permintaan data',
        'request_revoke'    => 'Mencabut persetujuan data',
        'nda_view'          => 'Melihat dokumen NDA',
        'nda_download'      => 'Mengunduh dokumen NDA',
        'quota_update'      => 'Mengubah kuota download',
        'quota_reset'       => 'Mereset kuota download',
        'permission_grant'  => 'Mengubah izin akses OPD',
        'permission_revoke' => 'Mencabut izin akses OPD',
        'user_create'       => 'Membuat akun pengguna',
        'user_update'       => 'Mengubah akun pengguna',
        'user_deactivate'   => 'Mengubah status akun',
        'password_change'   => 'Mengubah password sendiri',
        'password_reset'    => 'Mereset kata sandi',
    ];

    private const ACTION_DESCRIPTIONS = [
        'login'             => 'Pengguna berhasil masuk.',
        'logout'            => 'Pengguna keluar dari aplikasi.',
        'login_failed'      => 'Percobaan masuk tidak berhasil.',
        'file_upload'       => 'Dataset baru dimasukkan ke sistem.',
        'file_download'     => 'File dataset diunduh dari sistem.',
        'file_view'         => 'Halaman detail dataset dibuka.',
        'file_delete'       => 'Dataset dipindahkan ke status terhapus.',
        'request_create'    => 'OPD mengirim permintaan akses data.',
        'request_revise'    => 'OPD memperbaiki permintaan yang sebelumnya ditolak.',
        'request_approve'   => 'Super Admin menyetujui permintaan akses data.',
        'request_reject'    => 'Super Admin menolak permintaan akses data.',
        'request_revoke'    => 'Super Admin mencabut persetujuan yang masih aktif.',
        'nda_view'          => 'Dokumen perjanjian kerahasiaan dibuka.',
        'nda_download'      => 'Dokumen perjanjian kerahasiaan diunduh.',
        'quota_update'      => 'Batas jumlah unduhan diubah.',
        'quota_reset'       => 'Hitungan kuota unduhan dimulai ulang.',
        'permission_grant'  => 'Daftar OPD yang boleh mengakses dataset diperbarui.',
        'permission_revoke' => 'Izin akses OPD dicabut.',
        'user_create'       => 'Akun pengguna baru dibuat.',
        'user_update'       => 'Data akun pengguna diperbarui.',
        'user_deactivate'   => 'Akun pengguna diaktifkan atau dinonaktifkan.',
        'password_change'   => 'Pengguna mengganti password akunnya sendiri.',
        'password_reset'    => 'Kata sandi pengguna diganti oleh Super Admin.',
    ];

    private const RESOURCE_LABELS = [
        'DataFile'    => 'Dataset',
        'DataRequest' => 'Permintaan data',
        'User'        => 'Akun pengguna',
    ];

    private const REASON_LABELS = [
        'rate_limited'                => 'terlalu banyak percobaan masuk',
        'user_not_found_or_inactive'  => 'username tidak ditemukan atau akun tidak aktif',
        'account_locked'              => 'akun sedang terkunci',
        'wrong_password'              => 'kata sandi salah',
    ];

    private const PERIOD_LABELS = [
        'daily'    => 'per hari',
        'weekly'   => 'per minggu',
        'monthly'  => 'per bulan',
        'lifetime' => 'selamanya',
    ];

    protected $fillable = [
        'user_id', 'action', 'resource_type', 'resource_id',
        'context', 'ip_address', 'user_agent',
        'dasar_hukum', 'tujuan', 'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'context'     => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public static function actionOptions(): array
    {
        return self::ACTION_LABELS;
    }

    public function getActionLabelAttribute(): string
    {
        return self::ACTION_LABELS[$this->action] ?? str_replace('_', ' ', $this->action);
    }

    public function getActionDescriptionAttribute(): string
    {
        return self::ACTION_DESCRIPTIONS[$this->action] ?? 'Aktivitas tercatat oleh sistem.';
    }

    public function getActionClassAttribute(): string
    {
        return match (true) {
            in_array($this->action, ['login']) => 'action-login',
            in_array($this->action, ['logout']) => 'action-logout',
            in_array($this->action, ['login_failed']) => 'action-failed',
            in_array($this->action, ['file_download', 'nda_download']) => 'action-download',
            in_array($this->action, ['file_upload']) => 'action-upload',
            in_array($this->action, ['request_approve', 'permission_grant', 'quota_update', 'quota_reset']) => 'action-approve',
            in_array($this->action, ['request_reject', 'request_revoke', 'file_delete']) => 'action-reject',
            default => 'action-default',
        };
    }

    public function getActorUsernameAttribute(): string
    {
        return $this->user->username ?? ($this->context['username'] ?? '-');
    }

    public function getActorInstitutionAttribute(): string
    {
        return $this->user->instansi ?? '-';
    }

    public function getResourceLabelAttribute(): string
    {
        if (!$this->resource_type) {
            return '-';
        }

        $label = self::RESOURCE_LABELS[$this->resource_type] ?? $this->resource_type;
        return $this->resource_id ? "{$label} #{$this->resource_id}" : $label;
    }

    public function getContextSummaryAttribute(): string
    {
        $context = $this->context ?? [];

        return match ($this->action) {
            'login_failed' => $this->loginFailedSummary($context),
            'file_upload' => $this->fileUploadSummary($context),
            'file_download' => $this->fileDownloadSummary($context),
            'file_delete' => $this->valueSummary($context, 'original_filename', 'File'),
            'request_create', 'request_revise' => $this->requestSubmissionSummary($context),
            'request_approve', 'quota_update', 'quota_reset' => $this->quotaSummary($context),
            'request_reject', 'request_revoke' => $this->valueSummary($context, 'catatan', 'Catatan'),
            'nda_view', 'nda_download' => $this->valueSummary($context, 'nda_filename', 'Dokumen'),
            'permission_grant' => $this->permissionSummary($context),
            'user_create' => $this->userCreateSummary($context),
            'user_update' => $this->userUpdateSummary($context),
            'user_deactivate' => $this->statusSummary($context),
            'password_change' => 'Password akun sendiri berhasil diperbarui.',
            'password_reset' => 'Password akun direset oleh Super Admin.',
            default => $this->fallbackSummary($context),
        };
    }

    private function loginFailedSummary(array $context): string
    {
        $username = $context['username'] ?? $this->actor_username;
        $reason = self::REASON_LABELS[$context['reason'] ?? ''] ?? ($context['reason'] ?? 'alasan tidak diketahui');

        return "Username: {$username}; penyebab: {$reason}.";
    }

    private function fileUploadSummary(array $context): string
    {
        $parts = [];
        if (!empty($context['original_filename'])) {
            $parts[] = 'File: ' . $context['original_filename'];
        }
        if (!empty($context['file_size'])) {
            $parts[] = 'Ukuran: ' . $this->formatBytes((int) $context['file_size']);
        }
        if (isset($context['allowed_admins']) && is_array($context['allowed_admins'])) {
            $parts[] = 'OPD diberi akses: ' . count($context['allowed_admins']);
        }

        return $parts ? implode('; ', $parts) . '.' : '-';
    }

    private function fileDownloadSummary(array $context): string
    {
        $parts = [];
        if (!empty($context['request_id'])) {
            $parts[] = 'Permintaan data #' . $context['request_id'];
        }
        if (!empty($context['downloaded_by_role']) && $context['downloaded_by_role'] === 'super_admin') {
            $parts[] = 'Unduhan langsung oleh Super Admin';
        }
        if (!empty($context['quota_download_count']) && !empty($context['quota_limit'])) {
            $parts[] = 'Kuota terpakai: ' . $context['quota_download_count'] . '/' . $context['quota_limit'];
        }

        return $parts ? implode('; ', $parts) . '.' : '-';
    }

    private function requestSubmissionSummary(array $context): string
    {
        if (!empty($context['data_file_id'])) {
            return 'Dataset yang diminta: #' . $context['data_file_id'] . '.';
        }

        return $this->fallbackSummary($context);
    }

    private function quotaSummary(array $context): string
    {
        $parts = [];
        if (!empty($context['max_downloads'])) {
            $period = self::PERIOD_LABELS[$context['quota_period'] ?? ''] ?? ($context['quota_period'] ?? '');
            $parts[] = trim('Kuota: ' . $context['max_downloads'] . 'x ' . $period);
        }
        if (!empty($context['catatan'])) {
            $parts[] = 'Catatan: ' . $context['catatan'];
        }

        return $parts ? implode('; ', $parts) . '.' : '-';
    }

    private function permissionSummary(array $context): string
    {
        $adminIds = $context['new_allowed_admins'] ?? null;
        if (is_array($adminIds)) {
            return 'Jumlah OPD yang diizinkan: ' . count($adminIds) . '.';
        }

        return $this->fallbackSummary($context);
    }

    private function userCreateSummary(array $context): string
    {
        $role = $context['role'] ?? null;
        $instansi = $context['instansi'] ?? null;

        $parts = array_filter([
            $role ? 'Peran: ' . $this->roleLabel($role) : null,
            $instansi ? 'Instansi/OPD: ' . $instansi : null,
        ]);

        return $parts ? implode('; ', $parts) . '.' : '-';
    }

    private function userUpdateSummary(array $context): string
    {
        $changes = $context['changes'] ?? [];
        if (!is_array($changes) || empty($changes)) {
            return $this->fallbackSummary($context);
        }

        $labels = [
            'name' => 'nama',
            'username' => 'username',
            'instansi' => 'instansi/OPD',
            'jabatan' => 'jabatan',
            'role' => 'peran',
        ];

        $changedFields = array_map(fn ($field) => $labels[$field] ?? $field, array_keys($changes));
        return 'Bagian yang diubah: ' . implode(', ', $changedFields) . '.';
    }

    private function statusSummary(array $context): string
    {
        $status = $context['new_status'] ?? null;
        if ($status === 'active') {
            return 'Status baru: aktif.';
        }
        if ($status === 'inactive') {
            return 'Status baru: nonaktif.';
        }

        return $this->fallbackSummary($context);
    }

    private function valueSummary(array $context, string $key, string $label): string
    {
        return !empty($context[$key]) ? "{$label}: {$context[$key]}." : '-';
    }

    private function fallbackSummary(array $context): string
    {
        $visible = collect($context)
            ->except(['url', 'method'])
            ->filter(fn ($value) => !is_array($value) && $value !== null && $value !== '')
            ->map(fn ($value, $key) => str_replace('_', ' ', $key) . ': ' . $value)
            ->values()
            ->all();

        return $visible ? implode('; ', $visible) . '.' : '-';
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 2) . ' MB';
        return round($bytes / 1073741824, 2) . ' GB';
    }

    private function roleLabel(string $role): string
    {
        return $role === 'super_admin' ? 'Super Admin' : 'Admin OPD';
    }

    /**
     * Record an audit event for personal data access.
     * Wajib sesuai UU PDP Pasal 47 - Pencatatan aktivitas pemrosesan.
     */
    public static function record(
        string $action,
        Model  $resource,
        array  $context = [],
        ?string $dasarHukum = null,
        ?string $tujuan = null
    ): void {
        static::create([
            'user_id'       => auth()->id(),
            'action'        => $action,
            'resource_type' => class_basename($resource),
            'resource_id'   => $resource->getKey(),
            'context'       => $context,
            'ip_address'    => request()->ip(),
            'user_agent'    => request()->userAgent() ?? '',
            'dasar_hukum'   => $dasarHukum,
            'tujuan'        => $tujuan,
            'occurred_at'   => now(),
        ]);
    }
}
