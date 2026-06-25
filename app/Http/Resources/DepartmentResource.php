<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'path' => $this->path,
            
            // Hierarchical relationships
            'parent' => new DepartmentResource($this->whenLoaded('parent')),
            'children' => DepartmentResource::collection($this->whenLoaded('children')),
            
            // Counts
            'members_count' => $this->whenCounted('memberships'),
            'tasks_count' => $this->whenCounted('tasks'),
            
            // Metadata
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}