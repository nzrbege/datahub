<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NdaTemplate extends Model
{
    public const TYPE_REQUEST_LETTER = 'request_letter';
    public const TYPE_BAST = 'bast';

    protected $fillable = [
        'template_type',
        'original_filename',
        'file_path',
        'file_type',
        'file_size',
        'file_hash',
        'is_active',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public static function active(string $type = self::TYPE_BAST): ?self
    {
        return self::where('template_type', $type)
            ->where('is_active', true)
            ->latest()
            ->first();
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_REQUEST_LETTER => 'Surat Permohonan Data',
            self::TYPE_BAST => 'BAST',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeOptions()[$this->template_type] ?? $this->template_type;
    }

    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
        return round($bytes / 1048576, 2) . ' MB';
    }
}
