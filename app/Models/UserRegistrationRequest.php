<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRegistrationRequest extends Model
{
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'instansi',
        'letter_filename',
        'letter_path',
        'letter_hash',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'created_user_id',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function createdUser()
    {
        return $this->belongsTo(User::class, 'created_user_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Verifikasi',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => $this->status,
        };
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
