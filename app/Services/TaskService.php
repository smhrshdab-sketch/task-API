<?php

namespace App\Services;

use App\Events\TaskCreated;
use App\Models\Task;
use App\Services\ContributeService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskService{
    public function __construct(ContributeService $contributeService){
        $this->contributeService = $contributeService;
    }
     public function createTask(array $data, $parentId): Task{
        logger('TaskService (createTask) [data,department_id]: ',[$data,current_department()]);
        return DB::transaction(function () use ($data, $parentId) {
            $nextPath = $this->getNextPathNumber($parentId);
            
            // Convert parent_id properly
            $parentIdForDb = ($parentId === null || $parentId === 'null' || $parentId === 0) 
                ? null
                : (int)$parentId;
                
            $task = Task::create([
                'department_id' => current_department()->id,
                'assignee_id' => current_membership()->id,
                'project_id' => 1,
                'parent' => $parentIdForDb,
                'title' => $data['title'],
                'path' => $nextPath,
                'description' => $data['description'],
                'status' => $data['status'],
                'deadline' => $data['deadline'],

            ]);
            //-------------
            logger('memberships_engaged and departments_engaged: ',[$data['memberships_engaged'],$data['departments_engaged']]);
            if (!empty($data['memberships_engaged'])) {
                event(new TaskCreated(
                    $task, 
                    $data['memberships_engaged'] // آرایه اینجا منتقل می‌شود
                ));
            }            
            //-------------
            if (!empty($data['departments_engaged'])) {
                $this->contributeService->attachDepartment(
                    $task, 
                    $data['departments_engaged']
                );
            }          
            Log::info('Task is created', [$task]);
            return $task;
        });        
    }

    // ------------------------

    public function updateTask(Task $task, array $data): Task{
        return DB::transaction(function () use ($task, $data) {

            $task->update($data);
            Log::info('Task is updated. Now : ', [
                'task_id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'priority' => $task->priority,
                'deadline' => $task->deadline
            ]);
            return $task->fresh();
        });
    }

    // ------------------------

    public function deleteTask(Task $task): void{
        if ($task->attachments()->exists()) {
            throw new \Exception('Task has attachments and cannot be deleted.');
        }
        $status = $task->delete($task->id);
        Log::info('Delete is done',[$status]);
        $task->fresh();        
    }
    //================
    public function getAttachments(Task $task){
        $cacheKey = "attachments_{$task->getMorphClass()}_{$task->id}";

        return cache()->remember(
            $cacheKey,
            now()->addMinutes(30),
            fn() => $task->attachments()->latest()->get()
        );
    }
    public function getCurrentUserTasksPaginated(){
        $perPage = 10;
        $membership = current_membership();
        
        if (!$membership) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }
        
        // Get IDs from both collections
        $ownIds = $membership->ownTasks->pluck('id')->toArray();
        $inIds = $membership->inTasks->pluck('id')->toArray();
        
        // Merge and unique IDs
        $taskIds = array_unique(array_merge($ownIds, $inIds));
        
        // Query tasks with these IDs
        $all = Task::whereIn('id', $taskIds)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        //========================
        logger('ownId,inId,taskId,all',[$ownIds,$inIds,$taskIds,$all]);
        return $all;
    }
    public function getCurrentUserTasks(): Collection{
        $membership = current_membership();
        
        if (!$membership) {
            return collect();
        }
        
        // Get both collections
        $ownTasks = $membership->ownTasks;      // Collection
        $inTasks = $membership->inTasks;        // Collection
        
        // Merge them
        $allTasks = $ownTasks->merge($inTasks);
        
        // Remove duplicates (if a task appears in both)
        $uniqueTasks = $allTasks->unique('id');
        
        // Sort by latest first
        $sortedTasks = $uniqueTasks->sortByDesc('created_at');
        
        return $sortedTasks->values(); // Reset keys
    }
    /**
     * Get all tasks (for admin)
     */
    public function getAllTasks(): Collection{
        return Task::orderBy('created_at', 'desc')->get();
    }
    //=============
    public function getMyTasksWithoutPagination(): Collection{
        $tasks = current_membership()->tasks;
        if($tasks->count() < 1){
            logger("You have no any task in your profile");
        }
        return $tasks;
    }

    public function getLastSibling($parentId){
        $query = Task::query();
        
        if ($parentId === null || $parentId === 'null') {
            $query->whereNull('parent');
        } else {
            $query->where('parent', (int)$parentId);
        }
        
        // Now path is integer, so sorting works correctly!
        $result = $query->orderBy('path', 'desc')->first();
        
        return $result;
    }
    public function getNextPathNumber($parentId){
        $lastSibling = $this->getLastSibling($parentId);
        
        if ($lastSibling) {
            // Path is now integer, so addition works correctly
            $nextPath = (int)$lastSibling->path + 1;            
            return $nextPath;
        }
        
        return 1;
    }
}
