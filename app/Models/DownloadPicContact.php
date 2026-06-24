<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DownloadPicContact extends Model
{
    protected $fillable = [
        'nama_pic',
        'nomor_hp',
        'keterangan',
        'updated_by',
    ];

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function current(): ?self
    {
        return self::latest()->first();
    }

    public function getWhatsappUrlAttribute(): string
    {
        $number = preg_replace('/\D+/', '', $this->nomor_hp);

        if (str_starts_with($number, '0')) {
            $number = '62' . substr($number, 1);
        }

        return 'https://wa.me/' . $number;
    }
}
