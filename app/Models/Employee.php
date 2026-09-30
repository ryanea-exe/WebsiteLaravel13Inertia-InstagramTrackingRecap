<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_code',
        'name',
        'department',
        'instagram_user_id',
        'instagram_username',
        'instagram_link_status',
        'instagram_linked_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'instagram_linked_at' => 'datetime',
        ];
    }

    public function instagramComments()
    {
        return $this->hasMany(InstagramComment::class, 'matched_employee_id');
    }
}
