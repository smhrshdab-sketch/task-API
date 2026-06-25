<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Facades\Cache;

class Membership extends Model
{
    /** @use HasFactory<\Database\Factories\MembershipFactory> */
    use HasFactory,Authorizable;
    protected $table = 'memberships';
    
    protected $fillable = [
        'account_id',
        'department_id',
        'role_id',
        'status',
        'permissions_override'
    ];
    protected $casts = [
        // 'permissions' => 'array',
        // 'permissions_override' => 'array',
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
    public function ownTasks(){
        return $this->hasMany(Task::class,'assignee_id');//بدون مشخص کردن ستون مشترک(ی که اسم متفاوتی داره!) رابطه کار نمیکند
    }
    public function inTasks(){
        return $this->hasManyThrough(
            Task::class,
            Engage::class,
            'contributor',  // Foreign key on Engage (membership_id)
            'id',           // Foreign key on Task
            'id',           // Local key on Membership
            'task'          // Local key on Engage (task_id)
        );
    }
    public function auditLogs(){
        return $this->hasMany(AuditLog::class, 'causer_id', 'id');
    }
    //======== Accessors ========
    public function getEffectivePermissionsAttribute(): array{
        $base = $this->role->permissions;
        //logger('(membership) base: ',[$base]);
        if (is_string($base)) {
            //logger('(membership) base is string. Now decode to JSON');
            $base = json_decode($base, true) ?? [];
        }

        if (!is_array($base)) {
            logger()->error('Invalid permissions format for role', [
                'role_id' => $this->role->id ?? null,
                'actual_value' => $base,
                'type' => gettype($base)
            ]);     
            // یا throw exception
            //throw new Exception('Role permissions must be array or JSON string');      
            // یا حداقل حفظ کردن overrideها بدون از دست رفتن context
            $base = [];
            //logger('(membership) finall base is: ',[$base]);
        }
        //logger('(membership) permissions_override is: ',[$this->permissions_override]);
        if(!is_array($this->permissions_override)){
            $casted = json_decode($this->permissions_override,true);
        }
        else{
            $casted = $this->permissions_override;
        }
        foreach ($casted ?? [] as $key => $value) {
            $base[$key] = $value;
        }
        //logger('(membership) finall base is: ',[$base]);
        return $base;
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
    //===========
    public function getCachedPermissions(): array{
        $key = "permissions:membership:{$this->id}";
        return Cache::remember($key, now()->addMinutes(30), fn () =>
            is_array($this->effective_permissions)
                ? $this->effective_permissions
                : []
        );
        // //dd('$key',$key,'CachedPermissions',$CachedPermissions);
        // return $CachedPermissions;
    }
}
