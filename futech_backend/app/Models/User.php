<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'firstname',
        'middlename',
        'lastname',
        'email',
        'password',
        'role',
        'pending_email',
        'pending_email_otp_verified',
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'pending_email_otp_verified' => 'boolean',
        ];
    }

    /**
     * Get all blogs for the user.
     */
    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }

    /**
     * Get all comments for the user.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }


    //     public function isAdmin(): bool
//     {
//         return $this->role === 'admin';
//     }

    /**
     * Get OTP relationship
     */
    public function otps()
    {
        return $this->hasMany(Otp::class, 'email', 'email');
    }
}
