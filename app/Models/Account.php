<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; // ✅ کلاس، نه trait
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Authenticatable implements JWTSubject
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'bio',
        'avatar_path',
        'address',
        'phone',
    ];

    protected $hidden = [
        'password',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function memberships(){
        return $this->hasMany(Membership::class);
    }
    /*
    |--------------------------------------------------------------------------
    | JWT implementation
    |--------------------------------------------------------------------------
    */

    public function getJWTIdentifier(){//داخل توکن، کدوم مقدار کاربر رو مشخص می‌کنه؟
        return $this->getKey();//معمولاً id
    }

    public function getJWTCustomClaims(){
        return [
        ];
    }
}
