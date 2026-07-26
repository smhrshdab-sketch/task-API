<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $membership = current_membership();
        
        // Add detailed logging
        Log::debug('StoreTaskRequest authorize check', [
            'membership' => $membership,
            'membership_id' => $membership ? $membership->id : null,
            'user_id' => $membership ? $membership->user_id : null,
            'account_id' => $membership ? $membership->account_id : null,
            'all_request_data' => $this->all(),
            'headers' => $this->headers->all(),
        ]);
        
        if (!$membership) {
            Log::warning('TaskAuthorizationService: No membership found');
            return false;
        }
        
        $result = $membership->account->can('create', new Task());
        
        Log::debug('Authorization result', [
            'result' => $result,
            'permission' => 'create',
            'account_id' => $membership->account_id
        ]);
        
        return $result;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array{
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
            'path' => 'nullable|integer',
            'parent_id' => 'nullable|exists:tasks,id',
            'status' => 'nullable|in:preparation,progressing,completed,stopped,suspended',
            'priority' => 'nullable|in:low,medium,high',
            'deadline' => 'nullable|date',
            'memberships_engaged' => 'required|array',
            'memberships_engaged.*' => 'integer',
            'departments_engaged' => 'required|array',
            'departments_engaged.*' => 'integer',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|file|max:102400',
        ];
    }
    public function messages(){
        return [
            'title.required' => 'Title is required',
            'status.in' => 'Invalid status value',
            'priority.in' => 'Invalid priority value',
            'attachments.*.max' => 'Each file must be less than 10MB',
        ];
    }
    protected function prepareForValidation(): void{
        if ($this->has('memberships_engaged') && is_string($this->memberships_engaged)) {
            $this->merge([
                'memberships_engaged' => json_decode($this->memberships_engaged, true),
            ]);
        }
        //================
        if ($this->has('departments_engaged') && is_string($this->departments_engaged)) {
            $this->merge([
                'departments_engaged' => json_decode($this->departments_engaged, true),
            ]);
        }
    }
}
