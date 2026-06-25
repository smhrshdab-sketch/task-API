<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array{
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:accounts,email'],
            'password' => ['required', 'string', 'min:8'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar_path' => ['nullable', 'string', 'max:1000'],
            'organization_id' => ['nullable', 'exists:organizations,id'], // Optional if you want to allow custom org
        ];
    }
    
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.unique' => 'This email is already registered',
            'password.min' => 'Password must be at least 8 characters',
        ];
    }
    
    protected function prepareForValidation(): void
    {
        // Set default organization_id if not provided
        if (!$this->has('organization_id')) {
            $this->merge([
                'organization_id' => 1 // Or get from current user's organization
            ]);
        }
    }
}