<?php

namespace App\Http\Requests;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool{
        $membership = current_membership();
        if (!$membership) {
            return false;
        }
        //return app(DepartmentPolicy::class)->create();
        //return $membership->account->can('create', Department::class);اسم کلاس را برمی گرداند 
        return $membership->account->can('department_create', new Department());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array{
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
    public function messages(): array{
        return [
            'title.required' => 'Department title is required'
        ];
    }
    
    /**
     * Prepare data for validation
     */
    
    
}
