<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'instansi',
        'jabatan',
        'password',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'locked_until' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // ─── Activity Log Config ───────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'username', 'email', 'instansi', 'jabatan', 'is_active'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "User {$eventName}")
            ->useLogName('user_management');
    }

    // ─── Relationships ─────────────────────────────────────────────────────────

    public function uploadedFiles()
    {
        return $this->hasMany(DataFile::class, 'uploaded_by');
    }

    public function filePermissions()
    {
        return $this->hasMany(DataFilePermission::class);
    }

    public function accessibleFiles()
    {
        return $this->belongsToMany(DataFile::class, 'data_file_permissions', 'user_id', 'data_file_id')
            ->withPivot(['can_download', 'can_view_metadata', 'expires_at', 'granted_at'])
            ->wherePivot('can_view_metadata', true)
            ->where(function ($q) {
                $q->whereNull('data_file_permissions.expires_at')
                    ->orWhere('data_file_permissions.expires_at', '>', now());
            });
    }

    public function dataRequests()
    {
        return $this->hasMany(DataRequest::class);
    }

    public function downloadLogs()
    {
        return $this->hasMany(DownloadLog::class);
    }

    public function utilizationEvaluations()
    {
        return $this->hasMany(DataUtilizationEvaluation::class);
    }

    // ─── Helper Methods ────────────────────────────────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isBadanPerencanaanAdmin(): bool
    {
        return $this->isAdmin()
            && str_contains(
                strtolower((string) $this->instansi),
                'badan perencanaan pembangunan, riset, dan inovasi daerah'
            );
    }

    public function canAccessRequestMonitoring(): bool
    {
        return $this->isSuperAdmin() || $this->isBadanPerencanaanAdmin();
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    public function canAccessFile(DataFile $file): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->filePermissions()
            ->where('data_file_id', $file->id)
            ->where('can_download', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    public function incrementFailedLogin(): void
    {
        $this->increment('failed_login_attempts');
        if ($this->failed_login_attempts >= 5) {
            $this->update(['locked_until' => now()->addMinutes(30)]);
        }
    }

    public function resetFailedLogin(): void
    {
        $this->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ]);
    }
}
