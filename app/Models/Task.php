<?php

namespace App\Models;
use App\Contracts\Attachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Task extends Model implements Attachable
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory,SoftDeletes;
    //------------
    protected $fillable = [
        'project_id',
        'department_id',
        'assignee_id',
        'parent',
        'title',
        'path',
        'description',
        'status',
        'priority',
        'deadline',
    ];
    public const TYPE = '1';
     public function assigneeAccount()
    {
        return $this->assignee ? $this->assignee->account : null;
    }
    public function project(){
        return $this->belongsTo(Project::class);
    }
   //---------
    public function department(){
        return $this->belongsTo(Department::class);
    }

   //---------
    public function assignee(){
        return $this->belongsTo(Membership::class, 'assignee_id');//بدون مشخص کردن ستون مشترک(ی که اسم متفاوتی داره!) رابطه کار نمیکند
    }
   //----------
    public function tags(){
        return $this->belongsToMany(Tag::class);
    }
   //----------
    public function attachments(){
        return $this->morphMany(Attachment::class, 'attachable');
    }
    /**
     * Check if task can have attachments
     */
    public function canHaveAttachments(): bool
    {
        return !$this->trashed();
    }
    
    /**
     * Get folder name for storing attachments
     */
    public function getAttachmentFolderName(): string
    {
        return 'tasks';
    }
    //===========
    public function getCachedAttachents(): array{
        $key = "attachments_{".Task::class."}_{$this->id}";

        return Cache::remember($key, now()->addMinutes(120), fn () =>
            $this->attachments()
        );
    }
}
