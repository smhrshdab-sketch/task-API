<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model{
    /** @use HasFactory<\Database\Factories\PermissionFactory> */
    use HasFactory;
     protected $fillable = [
        'slug',
        'target',
        'subject',
        'is_active',
        'description'
    ];
    protected $casts = [
        'is_active' => 'boolean',
    ];
    public function roles(){
        return $this->belongsToMany(Role::class);
    }
}
