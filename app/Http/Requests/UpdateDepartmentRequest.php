<?php

namespace App\Http\Requests;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $department = $this->route('department');

        if (!$department instanceof Department) {
            return false;
        }

        return $this->user()->can('update', $department);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            //'status' => 'nullable|string'
        ];
    }
    public function messages(): array
    {
        return [
            'title.required' => 'Department title is required',
        ];
    }
}
