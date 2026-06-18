<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataFilePermission extends Model
{
    protected $fillable = [
        'data_file_id', 'user_id', 'granted_at', 'expires_at',
        'granted_by', 'can_download', 'can_view_metadata',
    ];

    protected function casts(): array
    {
        return [
            'granted_at' => 'datetime',
            'expires_at' => 'datetime',
            'can_download' => 'boolean',
            'can_view_metadata' => 'boolean',
        ];
    }

    public function dataFile()
    {
        return $this->belongsTo(DataFile::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
