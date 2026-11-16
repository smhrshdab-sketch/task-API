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
            'avatar_path' => ['nullable', 'string', 'max:1000']
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
        //just for prepare or casting data not for set domain vaalues!!
    }
}