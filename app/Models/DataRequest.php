<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DataRequest extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id', 'data_file_id', 'alasan_permintaan', 'tujuan_penggunaan',
        'dasar_hukum', 'nda_filename', 'nda_path', 'nda_hash',
        'status', 'reviewed_by', 'reviewed_at', 'catatan_reviewer',
        'download_token', 'token_expires_at', 'download_count', 'max_downloads',
        'quota_period', 'quota_reset_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'token_expires_at' => 'datetime',
            'quota_reset_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'reviewed_by', 'catatan_reviewer'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn($e) => "DataRequest {$e}")
            ->useLogName('data_request');
    }

    // ─── Relationships ─────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dataFile()
    {
        return $this->belongsTo(DataFile::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function downloadLogs()
    {
        return $this->hasMany(DownloadLog::class);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isApproved(): bool   { return $this->status === 'approved'; }
    public function isRejected(): bool   { return $this->status === 'rejected'; }
    public function isRevoked(): bool    { return $this->status === 'revoked'; }

    public function canDownload(): bool
    {
        return $this->isApproved()
            && $this->download_token !== null
            && $this->token_expires_at !== null
            && $this->token_expires_at->isFuture()
            && $this->remainingDownloads() > 0;
    }

    public function generateDownloadToken(): string
    {
        $token = Str::random(64);
        $this->update([
            'download_token' => hash('sha256', $token),
            'token_expires_at' => $this->quotaWindowEndsAt(),
        ]);
        return $token; // return raw token to be shown once
    }

    public function ensureDownloadToken(): void
    {
        if (
            $this->isApproved()
            && $this->remainingDownloads() > 0
            && (
                $this->download_token === null
                || $this->token_expires_at === null
                || $this->token_expires_at->isPast()
            )
        ) {
            $this->generateDownloadToken();
            $this->refresh();
        }
    }

    public function quotaPeriod(): string
    {
        return $this->quota_period ?: 'weekly';
    }

    public function quotaWindowStartsAt(): Carbon
    {
        $periodStart = match ($this->quotaPeriod()) {
            'daily' => now()->startOfDay(),
            'monthly' => now()->startOfMonth(),
            'lifetime' => $this->approvedStartDate(),
            default => now()->startOfWeek(),
        };

        if ($this->quota_reset_at && $this->quota_reset_at->gt($periodStart)) {
            return $this->quota_reset_at;
        }

        return $periodStart;
    }

    public function quotaWindowEndsAt(): Carbon
    {
        return match ($this->quotaPeriod()) {
            'daily' => now()->endOfDay(),
            'monthly' => now()->endOfMonth(),
            'lifetime' => now()->addYears(10),
            default => now()->endOfWeek(),
        };
    }

    public function quotaDownloadCount(): int
    {
        return $this->downloadLogs()
            ->where('status', 'success')
            ->where('downloaded_at', '>=', $this->quotaWindowStartsAt())
            ->count();
    }

    public function weeklyDownloadCount(): int
    {
        return $this->quotaPeriod() === 'weekly'
            ? $this->quotaDownloadCount()
            : $this->downloadLogs()
                ->where('status', 'success')
                ->whereBetween('downloaded_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count();
    }

    public function remainingDownloads(): int
    {
        return max(0, $this->max_downloads - $this->quotaDownloadCount());
    }

    public function remainingWeeklyDownloads(): int
    {
        return $this->remainingDownloads();
    }

    public function quotaPeriodLabel(): string
    {
        return match ($this->quotaPeriod()) {
            'daily' => 'per hari',
            'monthly' => 'per bulan',
            'lifetime' => 'selamanya',
            default => 'per minggu',
        };
    }

    public function quotaWindowLabel(): string
    {
        return match ($this->quotaPeriod()) {
            'daily' => 'hari ini',
            'monthly' => 'bulan ini',
            'lifetime' => 'selamanya',
            default => 'minggu ini',
        };
    }

    private function approvedStartDate(): Carbon
    {
        return $this->reviewed_at ?: $this->created_at ?: now()->startOfDay();
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'  => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'revoked'  => 'Dicabut',
            default    => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'  => 'yellow',
            'approved' => 'green',
            'rejected' => 'red',
            'revoked'  => 'gray',
            default    => 'gray',
        };
    }
}
