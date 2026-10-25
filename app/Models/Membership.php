<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Facades\Cache;

class Membership extends Model{
    /** @use HasFactory<\Database\Factories\MembershipFactory> */
    use HasFactory,Authorizable;
    protected $table = 'memberships';
    
    protected $fillable = [
        'account_id',
        'department_id',
        'organization_id',
        'role_id',
        'status',
        'permissions_override'
    ];
    protected $casts = [
        'permissions_override' => 'array',
    ];
    //-----------------
    public function role(){
        return $this->belongsTo(Role::class);
    }
    public function account(){
        return $this->belongsTo(Account::class);
    }
    public function department(){
        return $this->belongsTo(Department::class);
    }
    public function engage(){
        return $this->hasMany(Engage::class,'contributor');
    }
    public function ownTasks(){
        return $this->hasMany(Task::class,'assignee_id');
    }
    public function inTasks(){
        return $this->hasManyThrough(
            Task::class,
            Engage::class,
            'task',        // ✅ Engage.task → Task.id
            'id',          // ✅ Task.id
            'id',          // ✅ Membership.id
            'contributor'  // ✅ Engage.contributor → Membership.id
        );
    }
    public function auditLogs(){
        return $this->hasMany(AuditLog::class, 'causer_id', 'id');
    }
    //======== Accessors ========
    public function getEffectivePermissions(): array
    {
        $rolePermissions = $this->role
        ->permissions()
        ->where('is_active', true)
        ->pluck('slug')
        ->all();

        $overrides = $this->permissions_override ?? [];

        return array_values(
            array_unique([
                ...$rolePermissions,
                ...$overrides,
            ])
        );
    }
    //full name with role
    public function getDisplayNameAttribute(){
        return "{$this->account->name} - {$this->role->title}";
    }
    //===========
    public function canAccessDepartment(Department $dept): bool{
        // اگر عضویت در همان دپارتمان است
        if ($this->department_id === $dept->id) {
            return true;
        }
        // یا اگر دپارتمانِ عضویت، والدِ دپارتمانِ تسک است
        return $this->department->isAncestorOf($dept);
    }
    //============
    public function hasPermission(string $permission): bool{
        return in_array(
            $permission,
            $this->getEffectivePermissions(),
            true
        );
    }
}
