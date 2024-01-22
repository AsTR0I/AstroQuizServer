<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    
    protected $fillable = [
        'name',
        'login',
        'email',
        'role_code',
        'password',
        'access_token'
    ];

    protected $table = 'users' ;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    public function getJWTIdentifier()
    {
        return $this->getKey(); // Assuming your primary key is 'id'
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    protected $hidden = [
        'email_verified_at',
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
