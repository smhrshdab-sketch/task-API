<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * 
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Basic fields
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description ?? 'No description provided',
            'status' => $this->status,
            'priority' => $this->priority,
            
            // Formatted fields
            'formatted_created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'formatted_updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'human_readable_date' => $this->created_at->diffForHumans(),
            
            // Computed fields (not in database)
            'is_overdue' => $this->deadline && $this->deadline < now(),
            'progress_percentage' => $this->calculateProgress(), // Custom method
            
            // Relationships (can be conditional)
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'assignee' => new AccountResource($this->whenLoaded('assignee')),
            'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),
            
            // Conditional fields (based on user permissions)
            'internal_notes' => $this->when($request->user()->isAdmin(), $this->internal_notes),
            
            // Counts for efficiency
            'attachments_count' => $this->whenCounted('attachments'),
            'comments_count' => $this->whenCounted('comments'),
        ];
    }
    
    /**
     * Custom method for calculating progress
     */
    private function calculateProgress(): int
    {
        // Custom logic here
        return match($this->status) {
            'completed' => 100,
            'in_progress' => 50,
            'pending' => 0,
            default => 0,
        };
    }
}