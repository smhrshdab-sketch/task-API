<?php

namespace App\Services;

use App\Contracts\Attachable;
use App\Models\Attachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;

class AttachmentService{
    
    public function createAttachment(UploadedFile $file, Attachable $attachable, bool $isPublic = false): Attachment{
        return DB::transaction(function () use ($file, $attachable, $isPublic) {
             // Check if the model can have attachments
            if (!$attachable->canHaveAttachments()) {
                throw new \Exception('This model cannot have attachments.');
            }
            
            // Get folder name from the model
            $folder = $attachable->getAttachmentFolderName();
            
            $path = $file->store(
                "{$folder}/" . now()->format('Y/m/d'),
                'public'
            );
            
            $currentMembership = current_membership();
            
            if (!$currentMembership) {
                throw new \Exception('No membership context found. Please select a department.');
            }
            
            return $attachable->attachments()->create([
                'original_name' => $file->getClientOriginalName(),
                'file_name'     => basename($path),
                'file_path'     => $path,
                'disk'          => 'public',
                'mime_type'     => $file->getMimeType(),
                'size'          => $file->getSize(),
                'uploaded_by'   => $currentMembership->id,
                'is_public'     => $isPublic,
            ]);
        });
    }
    //==============
    public function deleteAttachment(Attachment $attachment): void{
        DB::transaction(function () use ($attachment) {
            if (Storage::disk($attachment->disk)->exists($attachment->file_path)) {
                Storage::disk($attachment->disk)->delete($attachment->file_path);
            }
            $attachment->delete();
        });
    }
    /**
     * Get attachments for a model
     */
    public function getAttachments(Attachable $attachable): Collection{
        return $attachable->attachments()->latest()->get();
    }
}