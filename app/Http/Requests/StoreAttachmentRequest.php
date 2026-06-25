<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\Attachable;

abstract class StoreAttachmentRequest extends FormRequest
{
    /**
     * Get the attachable model from the route
     */
    abstract protected function getAttachableModel(): ?Attachable;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $attachable = $this->getAttachableModel();
        
        if (!$attachable) {
            return false;
        }
        
        $membership = current_membership();
        
        if (!$membership) {
            return false;
        }
        
        // Check if the model can have attachments
        if (!$attachable->canHaveAttachments()) {
            return false;
        }
        
        // You can add additional authorization here
        // For example, check if user has permission for this specific model
        return $this->authorizeAttachment($attachable);
    }
    /**
     * Additional authorization logic for specific models
     * Override this in child classes
     */
    protected function authorizeAttachment(Attachable $attachable): bool{
        // Default implementation - always true
        // Override in specific request classes
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
         return [
            'attachment' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,doc,docx'],
            'is_public' => ['sometimes', 'boolean'],
        ];
    }
    protected function prepareForValidation(): void
    {
        if ($this->has('is_public')) {
            $this->merge([
                'is_public' => filter_var($this->is_public, FILTER_VALIDATE_BOOLEAN)
            ]);
        }
    }
}
