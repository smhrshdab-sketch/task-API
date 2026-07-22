<?php

namespace App\Http\Requests;

use App\Models\Engage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class StoreEngageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool{
        $membership = current_membership();
            if(!$membership){
                Log::warning('EngageAuthorizationService: No membership found');
                return false;
            }
        $result = $membership->account->can('create',Engage::class);
        logger("engage (store request) authorizel",[$result]);
        return $result;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array{
            return [
            'description' => 'nullable|string',
            'status' => 'nullable|string',
        ];
    }
     public function messages(): array{
        return [
            'description' => 'nullable|string',
            'status' => 'nullable|string',
        ];
    }
}
