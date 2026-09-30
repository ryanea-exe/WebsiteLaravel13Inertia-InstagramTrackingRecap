<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'instagram_account_id',
        'sync_type',
        'status',
        'started_at',
        'finished_at',
        'records_processed',
        'error_message',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function instagramAccount()
    {
        return $this->belongsTo(InstagramAccount::class);
    }
}
