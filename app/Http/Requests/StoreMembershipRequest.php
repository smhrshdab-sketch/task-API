<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMembershipRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool{
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array{
            return [
            'account_id' => ['required', 'exists:accounts,id'],
            'organization_id' => ['required', 'exists:organizations,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['required', 'in:active,suspended'],
            'permissions_override' => ['nullable', 'json']
        ];
    }
     public function messages(): array{
        return [
            'account_id.required' => 'Account ID is required',
            'account_id.exists' => 'The selected account does not exist',
            'organization_id.required' => 'Organization ID is required',
            'organization_id.exists' => 'The selected organization does not exist',
            'department_id.exists' => 'The selected department does not exist',
            'role_id.required' => 'Role ID is required',
            'role_id.exists' => 'The selected role does not exist',
            'status.required' => 'Status is required',
            'status.in' => 'Status must be either active or suspended',
            'permissions_override.array' => 'Permissions override must be a valid JSON object',
        ];
    }
}
