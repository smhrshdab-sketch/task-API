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
        'organization_id',
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
            'org_id' => $this->organization_id,
            'pv' => $this->permissions_version,
        ];
    }
    //محاسبه کلیه حق دسترسی ها(استاندارد + استثنا) برای یک حساب خاص و ذخیره یا بازیابی آن برای 30دقیقه در کش
    public function getAllPermissions(): array{
        return cache()->remember(
            "user_permissions_{$this->id}",
            now()->addMinutes(30),
            function () {
                $permissions = [];

                foreach ($this->memberships as $membership) {

                    // permissions role
                    $permissions = array_merge(
                        $permissions,
                        $membership->role->permissions ?? []
                    );

                    // override permissions
                    if (is_array($membership->permissions_override)) {
                        $permissions = array_merge(
                            $permissions,
                            $membership->permissions_override
                        );
                    }
                }

                return array_unique($permissions);
            }
        );
    }
    //چک کردن یک حق دسترسی در میان کل حق دسترسی ها(ی کاربر خاص)
    public function hasPermission(string $permission): bool{
        return in_array($permission, $this->getAllPermissions());
        //return in_array($permission, $this->permissions ?? []);
    }
    // آیا برای او (کاربر فراخواننده تابع) نقش داده شده ثبت شده؟
    public function hasRole(string $roleName): bool{
        return $this->memberships
            ->pluck('role.title')
            ->contains($roleName);
    }
    public function roleTitleFor(Department $department): ?string{
        $membership =  $this->memberships()
            ->where('department_id', $department->id)
            ->where('status', 'active')
            ->with('role')->first();
            //dump("roleTitleFor : ",$membership);
            return $membership?->role->title;
    }
    /*
    «برای این کاربر، کدوم عضویت (ممبرشیپ) مربوط به این دپارتمانه؟»
    چون:
        یک حساب(اکانت) ممکنه چند عضویت داشته باشه
        در دپارتمان‌های مختلف
        با نقش‌ها و مجوزهای مختلف
     */
    public function membershipForTask(Task $task): ?Membership{//آیا اکانت مورد نظر(فراخواننده این تابع)عضوی از تیم تسک است؟ یا واحدش بالاتر واحد تسک داده شده است؟
        return $this->memberships
            ->first(function ($m) use ($task) {
                return $m->department_id === $task->department_id
                    || $m->department->isAncestorOf($task->department);
            });
    }
}
