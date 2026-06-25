<?php

namespace App\Http\Requests;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;

class ViewAccountsRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Check if user is authorized to view accounts
        // You can add role/permission check here
        $membership = current_membership();
            if(!$membership){
                logger('There is no valid membership');
                return false;
            }
        $result = $membership->account->can('viewAny',Account::class);
        //logger("update authorize",[$membership,$result]);
        logger("viewAny authorizel",[$result]);
        return $result;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:255'],
            'sort_by' => ['nullable', 'string', 'in:id,name,email,created_at'],
            'sort_direction' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }

    public function messages(): array
    {
        return [
            'per_page.max' => 'Maximum 100 records per page',
        ];
    }
}