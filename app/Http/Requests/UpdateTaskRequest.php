<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool{
        $membership = current_membership();
        logger('Task request (update) [request: ',[$membership]);
            if(!$membership){
                Log::warning('TaskAuthorizationService: No membership found');
                return false;
            }
        $result = $membership->account->can('update',new Task());
        logger("UpdateTaskRequest authorize returned : ",[$result]);
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
            'priority' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
        ];
    }
    protected function prepareForValidation(): void{
        //Log::debug('Request data after preparation', $this->all());
    }
}
