<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NdaTemplate extends Model
{
    protected $fillable = [
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

    public static function active(): ?self
    {
        return self::where('is_active', true)->latest()->first();
    }

    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
        return round($bytes / 1048576, 2) . ' MB';
    }
}
