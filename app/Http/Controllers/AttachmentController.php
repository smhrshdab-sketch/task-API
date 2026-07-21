<?php

namespace App\Http\Controllers;

use App\Contracts\Attachable;
use App\Http\Requests\StoreTaskAttachmentRequest;
use App\Models\Attachment;
use App\Models\Task;
use App\Services\AttachmentService;
use Illuminate\Foundation\Http\FormRequest;

class AttachmentController extends Controller
{
    protected AttachmentService $attachmentService;

    public function __construct(AttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
    }
    // Separate method for each type
    //برای هر مدل لازمه که این تابع مخصوص خودش رو بنویسی که بعدا در تابع ذخیره ازس ش استفاده کنی
    public function storeTaskAttachment(StoreTaskAttachmentRequest $request, Task $task){
        //Log::info('💀 storeTaskAttachment Attachment CONTROLLER REACHED 💀');
        return $this->store($request, $task);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(){
        // Get tasks for the authenticated user
        $attachments = Attachment::where('task_id', auth('api')->user()->id)->get();
        
        return response()->json([
            'success' => true,
            'data' => $attachments,
            'message' => 'Tasks retrieved successfully'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FormRequest $request, Attachable $attachable){
        //Log::info('🔵👿🔵 store Attachment CONTROLLER REACHED 🔵👿🔵');
        logger('FormRequest,Attachable',[$request,$attachable]);
        try {
            $file = $request->file('attachment');
            
            // Build options array from request
            $options = [
                'is_public' => $request->input('is_public', false),
                'disk' => $request->input('disk', 'public'),
                'description' => $request->input('description', null),
            ];
            
            $attachment = $this->attachmentService->createAttachment(
                $file,
                $attachable,
                $options
            );
            logger('Attachment service:options, attachment',[$options,$attachment]);
            return response()->json([
                'success' => true,
                'data' => $attachment,
                'message' => 'Attachment uploaded successfully'
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload attachment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attachment $attachment){
        try {
            $parent = $attachment->attachable;
            // Verify attachment belongs to this task
            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'The parent model for this attachment no longer exists.'
                ], 404);
            }
            
            // Authorize deletion
            $currentMembership = current_membership();
            if (!$currentMembership) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. No membership context found.'
                ], 403);
            }
            
            $this->attachmentService->deleteAttachment($attachment);
            
            return response()->json([
                'success' => true,
                'message' => 'Attachment deleted successfully.',
                'data' => [
                    'attachment_id' => $attachment->id,
                    'parent_type' => class_basename($parent),
                    'parent_id' => $parent->id
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete attachment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
