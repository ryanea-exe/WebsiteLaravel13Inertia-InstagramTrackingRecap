<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'photo', 'role', 'status', 'last_login_at', 'seksi_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi ke model Seksi.
     */
    public function seksi()
    {
        return $this->belongsTo(Seksi::class);
    }

    /**
     * Cek apakah user adalah Administrator.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'Administrator';
    }

    /**
     * Cek apakah user adalah Staff.
     */
    public function isStaff(): bool
    {
        return $this->role === 'Staff';
    }
}
