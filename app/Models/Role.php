<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Role extends Model
{
    /** @use HasFactory<\Database\Factories\RoleFactory> */
    use HasFactory;
    protected $fillable = [
        'title',
        'slug',
        'description',
    ];
    //============ 
    public function memberships(){
        return $this->hasMany(Membership::class);
    }
    public function parent(){
        return $this->belongsTo(Role::class, 'parent_id');
    }

    public function children(){
        return $this->hasMany(Role::class, 'parent_id');
    }

    public function permissions(){
        return $this->belongsToMany(Permission::class);
    }
}
