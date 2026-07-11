<?php

namespace App\Contracts;

interface Attachable//مدل صاحب ضمیمه
{
    /**
     * Get the attachments relationship
     */
    public function attachments();
    
    /**
     * Check if the model can have attachments
     */
    public function canHaveAttachments(): bool;
    
    /**
     * Get the folder name for storing attachments
     */
    public function getAttachmentFolderName(): string;
}