<?php

namespace App\Http\Requests;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;

class ViewDepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $membership = current_membership();
            if(!$membership){
                logger('There is no valid membership');
                return false;
            }
        $result = $membership->account->can('viewAny',Department::class);
        //logger("update authorize",[$membership,$result]);
        logger("viewAny authorize",[$result]);
        return $result;
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
