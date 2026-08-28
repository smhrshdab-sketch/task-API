<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\ViewTaskRequest;
use App\Models\Account;
use App\Models\Attachment;
use App\Models\Task;
use App\Services\AttachmentService;
use App\Services\AuditLogService;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    protected TaskService $taskService;
    protected AttachmentService $attachmentService;

    public function __construct(TaskService $taskService,AttachmentService $attachmentService){
        $this->taskService = $taskService;
        $this->attachmentService = $attachmentService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(ViewTaskRequest $request){ 
        //Log::info('🔵🔵🔵 Index Task CONTROLLER REACHED 🔵🔵🔵');
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
        //logger(' *#* task_controller_show ** ');
        $result = Task::with([
            'assignee.account',
            'contributors.membership.account',
            'contributors.membership.role',
            'departments.department',
            'attachments'
        ])->findOrFail($task->id);
        app(AuditLogService::class)->record($task, 'viewed', [
            'description' => 'Task viewed',
            'metadata' => ['table' => $task->getTable()],
        ]);
        //logger('[task_controller_show]result: ',[$result]);
        return response()->json([
            'success' => true,
            'data' => $result,
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
        logger('(TaskController_store) attachments is: ',[$request->file('attachments')]);
        $parentId = $request->input('parent_id');
        
        if (empty($parentId) || $parentId === 0 || $parentId === '0') {
            $parentId = null;
        }
        $task = $this->taskService->createTask(
            $request->validated(),
            $parentId
        );
        logger('Created task is: ',[$task]);
        return response()->json([
            'success' => true,
            'data' => $task,
            'message' => 'Task created successfully'
        ], 201);
    }

    // --------------------

    public function update(UpdateTaskRequest $request, Task $task){
        Log::info('👿 TaskController update REACHED 👿');
        logger('Task: ',[$task]);
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
        //$this->authorize('attachments', $task);
        Log::info('👿 TaskController_attachments REACHED 👿');
        $attachments = $task->attachments;
        if($attachments){
            logger('task,attachment => ',[$task->title,$attachments]);
        }
        else{
            log('there is no attachment for '.$task->title.' task!');
        }
        return response()->json([
            'success' => true,
            'data' => [
                'attachments' => $attachments,
                'total' => $attachments->count()
            ]
        ]);
    }
    public function updateMembers(Request $request, $taskId){
        //Log::info('👿 TaskController_updateMembers REACHED 👿');
        $membership = current_membership();        
            if(!$membership){
                Log::warning('TaskAuthorizationService: No membership found');
                return false;
            }
        $validated = $request->validate([
            'memberships_engaged' => 'nullable|array',
            'memberships_engaged.*' => 'exists:memberships,id',//به این معنی است که آیم ود ننظر باید در مدل/جدول ذکر شده موجود باشد
        ]);
        //logger('$validated : ',[$validated]);
        $memberships = $request->input('memberships_engaged',[]);
        //logger('request, taskId, memberships: ',[$request,$taskId,$memberships]);
        $task = Task::findOrFail($taskId);
        //logger('task: ',[$task]);
        $syncData = [];
        foreach($memberships as $membershipId){
            $syncData[$membershipId] = [
                'contributed_by' => $membership->id,
                'status' => 'active',
                'description' => 'Ay jan ...'
            ];
        }
        $task->memberships()->sync($syncData);
        //logger('task: ',[$task]);
        return response()->json(['message' => 'Updated successfully']);
    }
    public function addAttachments(Request $request,Task $task){
        //Log::info('👿 TaskController_addAttachments REACHED 👿');
        //logger('task,attachment: ',[$task, $request->file('attachments')]);
        $request->validate([
            'attachments'   => 'required|array|min:1',
            'attachments.*' => 'file|max:10240',
        ]);
    
        $files = $request->file('attachments');
        Log::info('Received files: ', ['count' => count($files)]);
        $options['is_public'] = true;
        $options['disk'] = 'public';
        $options['description'] = null;
        foreach ($files as $file) {
            logger('files: ',[$file->getClientOriginalName()]);
            if ($file instanceof UploadedFile){
                $this->attachmentService->createAttachment(                    
                    $file,
                    $task, 
                    $options
                );
            }
        }
        Log::info('attachments are attached'); 
        return response()->json([
            'success' => true,
            'message' => "congragulation you got _addAttachments_ function"
        ]);
    }
    public function removeAttachments(Request $request,Task $task){
        Log::info('👋 TaskController_removeAttachments REACHED 👋');        
        $request->validate([
            'attachments'   => 'required|array|min:1',
            'attachments.*' => 'integer',
        ]);
        $attachmentIds = $request->input('attachments');
        logger('task,attachment: ',[$task, $attachmentIds]);
        foreach ($attachmentIds as $id) {
            logger('attachment id: ',[$id]);
            $attachment = Attachment::find($id);
        
            if ($attachment) {                
                $this->attachmentService->deleteAttachment(                    
                    $attachment
                );
            }            
        }
        Log::info('attachments are deleted'); 
        return response()->json([
            'success' => true,
            'message' => "congragulation you got _removeAttachments_ function"
        ]);
    }

}
