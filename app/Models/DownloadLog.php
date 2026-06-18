<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DownloadLog extends Model
{
    protected $fillable = [
        'user_id', 'data_file_id', 'data_request_id',
        'ip_address', 'user_agent', 'captcha_passed',
        'status', 'keterangan', 'downloaded_at',
    ];

    protected function casts(): array
    {
        return [
            'captcha_passed' => 'boolean',
            'downloaded_at' => 'datetime',
        ];
    }

    public function user()        { return $this->belongsTo(User::class); }
    public function dataFile()    { return $this->belongsTo(DataFile::class); }
    public function dataRequest() { return $this->belongsTo(DataRequest::class); }
}
