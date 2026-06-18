<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DataFile extends Model
{
    use SoftDeletes, LogsActivity;

    public const CATEGORY_LABELS = [
        'DATASET_KELUARGA' => 'Dataset Keluarga',
        'DATASET_ANGGOTA_KELUARGA' => 'Dataset Anggota Keluarga',
    ];

    protected $fillable = [
        'judul', 'deskripsi', 'original_filename', 'stored_filename',
        'file_path', 'file_type', 'file_size', 'file_hash',
        'is_encrypted', 'kategori', 'jumlah_record', 'wilayah',
        'tahun_data', 'is_active', 'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
            'is_active' => 'boolean',
            'file_size' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(fn(string $event) => "DataFile {$event}")
            ->useLogName('data_file');
    }

    // ─── Relationships ─────────────────────────────────────────────────────────

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function permissions()
    {
        return $this->hasMany(DataFilePermission::class);
    }

    public function allowedUsers()
    {
        return $this->belongsToMany(User::class, 'data_file_permissions', 'data_file_id', 'user_id')
            ->withPivot(['can_download', 'can_view_metadata', 'expires_at', 'granted_by']);
    }

    public function dataRequests()
    {
        return $this->hasMany(DataRequest::class);
    }

    public function downloadLogs()
    {
        return $this->hasMany(DownloadLog::class);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 2) . ' MB';
        return round($bytes / 1073741824, 2) . ' GB';
    }

    public function getKategoriLabelAttribute(): string
    {
        return self::CATEGORY_LABELS[$this->kategori] ?? $this->kategori ?? '-';
    }
}
