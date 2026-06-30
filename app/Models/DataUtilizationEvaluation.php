<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataUtilizationEvaluation extends Model
{
    protected $fillable = [
        'data_request_id',
        'data_file_id',
        'user_id',
        'report_filename',
        'report_path',
        'report_hash',
        'notes',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    public function dataRequest()
    {
        return $this->belongsTo(DataRequest::class);
    }

    public function dataFile()
    {
        return $this->belongsTo(DataFile::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
