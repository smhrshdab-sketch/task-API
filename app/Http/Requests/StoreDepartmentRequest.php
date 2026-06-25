<?php

namespace App\Http\Requests;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool{
        $membership = current_membership();
        //logger()->info('you are in StoreDepartmentRequest and you are: ',[$membership]);
        if (!$membership) {
            return false;
        }
        //return app(DepartmentPolicy::class)->create();
        //return $membership->account->can('create', Department::class);اسم کلاس را برمی گرداند 
        return $membership->account->can('create', new Department());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'organization_id' => 'required|exists:organizations,id',
            'parent_id' => 'nullable|exists:departments,id',
            'path' => 'nullable|',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ];
    }
    public function messages(): array
    {
        return [
            'organization_id.required' => 'Organization ID is required',
            'organization_id.exists' => 'The selected organization does not exist',
            'parent_id.exists' => 'The selected parent department does not exist',
            'path.required' => 'Department path is required',
            'title.required' => 'Department title is required',
        ];
    }
    
    /**
     * Prepare data for validation
     */
    
    
}
