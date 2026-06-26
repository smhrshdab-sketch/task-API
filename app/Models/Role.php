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
        'permissions',
        'description',
        'status'
    ];
    protected $casts = [
        //'permissions' => 'json',
    ];
    //=================
    /*public function setPermissionsAttribute($value){
        if (is_string($value)) {
            $value = json_decode($value, true);
        }
        $this->attributes['permissions'] = json_encode($value);
    }*/
    //============
    public const MANAGER = 'Manager';
    //============
    public function accounts(){
        return $this->belongsToMany(Account::class);
    }  
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
    public function getRoleCache(Role $role): array{
        $key = "role_{$role->id}";
        return Cache::remember($key, now()->addMinutes(30),$role->slug);
    }
}
