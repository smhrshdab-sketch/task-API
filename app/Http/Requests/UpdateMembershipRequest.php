<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMembershipRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Get the membership from the route
        $membership = $this->route('membership');
        
        // Check if user can update this specific membership
        return $this->user()->can('update', $membership);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'required', Rule::in(['active', 'suspended'])],
            'department_id' => ['sometimes', 'required', 'exists:departments,id'],
            'role_id' => ['sometimes', 'required', 'exists:roles,id'],
            'permissions_override' => ['nullable', 'json'],
        ];
    }
    
    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.in' => 'Status must be either active or suspended',
            'department_id.exists' => 'The selected department does not exist',
            'role_id.exists' => 'The selected role does not exist',
        ];
    }
}