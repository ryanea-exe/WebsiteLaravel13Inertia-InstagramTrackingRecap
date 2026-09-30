<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstagramMediaMetricSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'instagram_media_id',
        'captured_at',
        'likes',
        'comments_count',
        'shares',
        'saves',
        'reach',
        'views',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'captured_at' => 'datetime',
            'raw_payload' => 'array',
        ];
    }

    public function instagramMedia()
    {
        return $this->belongsTo(InstagramMedia::class);
    }
}
