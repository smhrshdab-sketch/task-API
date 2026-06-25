<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Department extends Model
{
    /** @use HasFactory<\Database\Factories\DepartmentFactory> */
    use HasFactory;
    protected $fillable = [
        'organization_id',
        'parent_id',
        'title',
        'path',
        'description',
        'status'    //اگه که متغیری رو اینجا یادت بره موقع بروزرسانی تغییری حاصل نمی شه!
    ];
     protected $casts = [
        'path' => 'integer',
        'parent_id' => 'integer',
        'organization_id' => 'integer'
    ];
    /*
            $table->text('description')->nullable();
     */
     public function organization(){
        return $this->belongsTo(Organization::class);
    }
    //============
    public function memberships(){
        return $this->hasMany(Membership::class);
    }

    public function attachments(){
        return $this->hasMany(Attachment::class);
    }
    
    public function projects(){
        return $this->hasMany(Project::class);
    }
    public function activeMemberships()
    {
        return $this->hasMany(Membership::class)->where('status', 'active');
    }
    
    public function accounts()
    {
        return $this->hasManyThrough(Account::class, Membership::class);
    }
    public function getDepartmentCache(Department $dep): array{
        $key = "department_{$dep->id}";
        return Cache::remember($key, now()->addMinutes(30),$dep->title);
    }
    //===========
    public function inCharge(){
        return $this->belongsTo(Membership::class);
    }
    //یک پدر دارد
    public function parent(){
        return $this->belongsTo(Department::class, 'parent_id');
    }
    //چند فرزند می تواند داشته باشد
    public function children(){
        return $this->hasMany(Department::class, 'parent_id');
    }
    public function isAncestorOf(Department $other): bool{
        return str_starts_with($other->path, $this->path . '.');//وجود اسلش یا خط تیره یا نقطه در آخر مسیر بععث جلوگیری از خطا می شود(Screenshot 2026-04-24 173828)
    }
    public function tasks(){
        return $this->hasMany(Task::class);
    }
    /**
     * Check if department can be moved up
     */
    public function canPullUp(): bool{
        return $this->parent_id;
    }
    
    /**
     * Check if department can be moved down
     */
    public function canPushDown(): bool{
        $maxParentId = Department::max('parent_id') ?? 0;
        return $this->parent_id < $maxParentId;
    }
     // Helper methods
    public function isRoot(): bool{
        return is_null($this->parent_id);
    }
    
    public function hasChildren(): bool{
        return $this->children()->exists();
    }

    public function getDepth(Department $dep): int{
        if($dep->parent){
            return 1 + $dep->getDepth($dep->parent);
        }
        return 0;
    }

    public static function getNextPath($parentId): int{
        $maxPath = static::where('parent_id','=', $parentId,true)->max('path');
        return $maxPath ? $maxPath + 1 : 1;
    }

    public static function boot(){
        parent::boot();
        
        static::creating(function ($department) {
            if (!$department->path) {
                $department->path = static::getNextPath($department->parent_id);
            }
        });
    }

    public function getFullPath(): string{
        $ancestors = $this->getAncestors();
        $path = [];
        
        foreach ($ancestors as $ancestor) {
            $path[] = $ancestor->title;
        }
        $path[] = $this->title;
        
        return implode(' / ', $path);
    }
    
    public function getAncestors(){
        $ancestors = collect();
        $parent = $this->parent;
        
        while ($parent) {
            $ancestors->prepend($parent);
            $parent = $parent->parent;
        }
        
        return $ancestors;
    }
    public function canBeDeleted(): array{
        $blockers = [];
        
        // Check for sub-departments
        if ($this->children()->exists()) {
            $blockers['children'] = [
                'count' => $this->children()->count(),
                'message' => 'sub-departments',
                'items' => $this->children()->pluck('title')->toArray()
            ];
        }
        
        // Check for members (employees)
        if ($this->memberships()->exists()) {
            $blockers['memberships'] = [
                'count' => $this->memberships()->count(),
                'message' => 'members assigned to this department',
                'items' => $this->memberships()->with('account')->get()->map(function($m) {
                    return $m->account->name;
                })->toArray()
            ];
        }
        
        // Check for tasks
        if ($this->tasks()->exists()) {
            $blockers['tasks'] = [
                'count' => $this->tasks()->count(),
                'message' => 'tasks in this department',
                'items' => $this->tasks()->pluck('title')->toArray()
            ];
        }
        
        // Check for attachments
        // if ($this->attachments()->exists()) {
        //     $blockers['attachments'] = [
        //         'count' => $this->attachments()->count(),
        //         'message' => 'attachments in this department',
        //         'items' => $this->attachments()->pluck('original_name')->toArray()
        //     ];
        // }
        
        return $blockers;
    }
    
    /**
     * Check if department can be deleted (simple boolean)
     */
    public function isDeletable(): bool
    {
        return empty($this->canBeDeleted());
    }
}
