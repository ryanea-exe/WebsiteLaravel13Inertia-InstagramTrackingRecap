<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportingPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_at',
        'end_at',
        'timezone',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
        ];
    }
}
