<?php

namespace App\Http\Requests;

use App\Models\Membership;
use App\Models\Permission;
use Illuminate\Foundation\Http\FormRequest;

class DelegatePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $permission = $this->route('permission');

			if (!$permission instanceof Permission) {
			    return false;
			}
			//===========
			
		$membership = $this->route('membership');

			if (!$membership instanceof Membership) {
			    return false;
			}

			return $this->user()->can('delegate', $permission,$membership );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
