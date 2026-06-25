<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        Log::info('UpdateRoleRequest@authorize STARTED');
        
        $membership = current_membership();
        
        if (!$membership) {
            Log::warning('No membership found');
            return false;
        }
        
        // Get the role from route (route model binding)
        $role = $this->route('role');
        
        if (!$role) {
            Log::warning('No role found in route parameters', [
                'route_params' => $this->route()->parameters()
            ]);
            return false;
        }
        
        Log::info('Role found for update', [
            'role_id' => $role->id,
            'role_title' => $role->title
        ]);
        
        // Pass the ACTUAL role instance
        $result = $membership->account->can('update', $role);
        
        Log::info('Authorization result', ['result' => $result]);
        
        return $result;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Get the role from route to ignore its own slug
        $role = $this->route('role');
        
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 
                'string',
                // Ignore current role's slug when checking uniqueness
                Rule::unique('roles')->ignore($role?->id)
            ],            
            'permissions' => ['nullable', 'json'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'title.required' => 'Title is required',
            'slug.required' => 'Slug is required',
            'slug.unique' => 'This slug already exists',
            'permissions.array' => 'Permissions must be a valid JSON object',
        ];
    }
    
    protected function prepareForValidation(): void{
        Log::debug('UpdateRoleRequest: prepareForValidation called');
        
        // Convert permissions object to array if needed
        logger('permissions(exist): ',[$this->has('permissions')]);
        logger('Is permissions object? : ',[is_object($this->permissions)]);
        logger('Is permissions array? : ',[is_array($this->permissions)]);
        //logger('Permissions type : ',[typeOf($this->permissions)]);
        if ($this->has('permissions') && !is_object($this->permissions)) {
            Log::info('Convert permissions object to array');
            logger('permissions: ',[$this->permissions]);
            // $this->merge(
            //     'permissions' => (array) $this->permissions
            // );
            Log::debug('Permissions converted from object to array');
        }
        
        Log::debug('Request data after preparation', $this->all());
    }
}