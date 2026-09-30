<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstagramMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'instagram_account_id',
        'external_media_id',
        'media_type',
        'product_type',
        'caption',
        'permalink',
        'media_url',
        'thumbnail_url',
        'published_at',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'raw_payload' => 'array',
        ];
    }

    public function instagramAccount()
    {
        return $this->belongsTo(InstagramAccount::class);
    }

    public function metricSnapshots()
    {
        return $this->hasMany(InstagramMediaMetricSnapshot::class);
    }

    public function comments()
    {
        return $this->hasMany(InstagramComment::class);
    }
}
