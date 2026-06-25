<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\ViewTaskRequest;
use App\Models\Account;
use App\Models\Task;
use App\Services\AuditLogService;
use App\Services\TaskService;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    protected TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(ViewTaskRequest $request){ 
        Log::info('🔵🔵🔵 Index Task CONTROLLER REACHED 🔵🔵🔵');
       try {
            $tasks = $this->taskService->getCurrentUserTasks();
            logger('Tasks: ',[$tasks]);
            return response()->json([
                'success' => true,
                'data' => $tasks,
                'total' => $tasks->count(),
                'message' => 'Tasks retrieved successfully'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve Tasks'
            ], 500);
        }
    }

    public function show(Task $task){
        $this->authorize('viewAny',$task);
        app(AuditLogService::class)->record($task, 'viewed', [
            'description' => 'Task viewed',
            'metadata' => ['table' => $task->getTable()],
        ]);
        return response()->json([
            'success' => true,
            'data' => $task,
            'message' => 'Task is retrieved successfully'
        ], 201);
    }

    /**
     * Show the form for creating a new resource.
     */
     public function create(Account $user): bool{
        return $user->hasPermission('task.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request){
        // Get parent_id from request, convert empty string or 0 to null
            $parentId = $request->input('parent_id');
            
            // Convert falsy values to null for root departments
            if (empty($parentId) || $parentId === 0 || $parentId === '0') {
                $parentId = null;
            }
        $task = $this->taskService->createTask(
            $request->validated(),
            $parentId
        );

        return response()->json([
            'success' => true,
            'data' => $task,
            'message' => 'Task created successfully'
        ], 201);
    }

    // --------------------

    public function update(UpdateTaskRequest $request, Task $task){
        $updatedTask = $this->taskService->updateTask(
            $task,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => $updatedTask,
            'message' => 'Task updated successfully'
        ]);
    }

    // --------------------

    public function destroy(Task $task){
        $this->authorize('delete', $task);

        $this->taskService->deleteTask($task);

        return response()->json([
            'success' => true,
            'message' => 'Task Deleted successfully'
        ]);
    }
    //-------------------
    public function attachments(Task $task){
        $this->authorize('attachments', $task);
        $attachments = $this->taskService->getAttachments($task);
        //
        return response()->json([
            'success' => true,
            'data' => [
                'task' => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'department_id' => $task->department_id
                ],
                'attachments' => $attachments,
                'total' => $attachments->count()
            ]
        ]);
    }
}
