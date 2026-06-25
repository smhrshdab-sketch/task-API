<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class StoreRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool{
        $membership = current_membership();
            if(!$membership){
                Log::warning('RoleAuthorizationService: No membership found');
                return false;
            }
        $result = $membership->account->can('create',Role::class);
        logger("role (store request) authorizel",[$result]);
        return $result;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array{
            return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'unique:roles,slug'],  
            'permissions' => ['nullable', 'json'],
            'description' => 'nullable|string',
            'status' => 'nullable|string',
        ];
    }
     public function messages(): array{
        return [
            'title.required' => 'Title ID is required',
            'slug.required' => 'slug ID is required',
            'permissions.array' => 'Permissions override must be a valid JSON object',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
        ];
    }
    protected function prepareForValidation(): void{
        logger("reach to prepareForValidation");
        if ($this->has('title')) {
            logger("prepareForValidation_title : ",[$this->has('title')]);
        }
        if ($this->has('slug')) {
            logger("prepareForValidation_slug : ",[$this->has('slug')]);
        }
        // Convert permissions object to array if needed
        if ($this->has('permissions') && is_object($this->permissions)) {
            // $this->merge([
            //     'permissions' => (array) $this->permissions
            // ]);
            logger("Permissions converted from object to array", [$this->permissions]);
        }
        // Log data for debugging
        logger("Request data after preparation", [
            'title' => $this->title,
            'slug' => $this->slug,
            'permissions' => $this->permissions,
            'description' => $this->description,
            'status' => $this->status,
        ]);
    }
}
