<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstagramComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'instagram_media_id',
        'external_comment_id',
        'commenter_instagram_user_id',
        'commenter_username',
        'matched_employee_id',
        'text',
        'commented_at',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'commented_at' => 'datetime',
            'raw_payload' => 'array',
        ];
    }

    public function instagramMedia()
    {
        return $this->belongsTo(InstagramMedia::class);
    }

    public function matchedEmployee()
    {
        return $this->belongsTo(Employee::class, 'matched_employee_id');
    }
}
