<?php

namespace App\Services;

use App\Contracts\Attachable;
use App\Exceptions\MembershipContextMissingException;
use App\Exceptions\ModelCannotHaveAttachmentsException;
use App\Exceptions\PathMissingExcepiton;
use App\Models\Attachment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AttachmentService{
    
    public function createAttachment(
        UploadedFile $file,
        Attachable $attachable,
        array $options = []
    ): Attachment
    {
        Log::info('🔵🔵🔵 store Attachment SERVICE REACHED 🔵🔵🔵');
        return DB::transaction(function () use ($file, $attachable, $options) {
            logger('file,attachable ',[$file,$attachable]);
            // 1️⃣ Extract options with defaults
            $isPublic = $options['is_public'] ?? false;
            $disk = $options['disk'] ?? 'public';
            $description = $options['description'] ?? null;
            logger('isPublic,disk,$description ',[$isPublic,$disk,$description]);
            // 2️⃣ Check if model can have attachments
            if (!$attachable->canHaveAttachments()) {
                //throw new \Exception('This model cannot have attachments.');
                throw new ModelCannotHaveAttachmentsException();
            }
            logger('canHaveAttachments passed ');
            // 3️⃣ Get folder name from the model
            $folder = $attachable->getAttachmentFolderName();
            logger('folder ',[$folder]);
            // 4️⃣ Store the file
            try {
                logger('Attempting to store file...');
                $path = $file->store(
                    "{$folder}/" . now()->format('Y/m/d'),
                    $disk
                );
                logger('Store success. Path: ' . $path);
            } catch (\Exception $e) {
                logger('CRITICAL ERROR in store: ' . $e->getMessage());
                throw $e; // دوباره خطا را پرتاب کن تا بفهمیم مشکل از کجاست
            }
            logger('path',[$path]);
            if (!$path) {
                throw new PathMissingExcepiton;
            }
            logger('path passed ');
            // 5️⃣ Get current membership
            $currentMembership = current_membership();
            
            if (!$currentMembership) {
                throw new MembershipContextMissingException();
            }
            logger('currentMembership passed ');
            // 6️⃣ Create attachment record
            return $attachable->attachments()->create([
                'original_name' => $file->getClientOriginalName(),
                'file_name'     => basename($path),
                'file_path'     => $path,
                'disk'          => $disk,
                'mime_type'     => $file->getMimeType(),
                'size'          => $file->getSize(),
                'uploaded_by'   => $currentMembership->id,
                'is_public'     => $isPublic,
                'description'   => $description,
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