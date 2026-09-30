<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstagramAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'facebook_page_id',
        'instagram_user_id',
        'username',
        'name',
        'account_type',
        'connection_status',
        'access_token',
        'token_expires_at',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'token_expires_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function media()
    {
        return $this->hasMany(InstagramMedia::class);
    }

    public function syncLogs()
    {
        return $this->hasMany(SyncLog::class);
    }
}
