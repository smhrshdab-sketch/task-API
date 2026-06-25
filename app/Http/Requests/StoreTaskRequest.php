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
   public function authorize(): bool{
        $membership = current_membership();
            if(!$membership){
                Log::warning('TaskAuthorizationService: No membership found');
                return false;
            }
        $result = $membership->account->can('create',new Task());
        return $result;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => 'required|string',
            'priority' => 'nullable|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
            'parent' => 'nullable|exists:departments,id',
            'path' => 'nullable|',
            'memberships_engaged' => 'nullable|array',
            'departments_engaged' => 'nullable|array'
        ];
    }
    protected function prepareForValidation(): void{
        //Log::debug('Request data after preparation', $this->all());
    }
}
