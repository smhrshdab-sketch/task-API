<?php

namespace App\Http\Requests;

use App\Models\Membership;
use Illuminate\Foundation\Http\FormRequest;

class ViewMembershipsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool{
        $membership = current_membership();
            if(!$membership){
                logger('There is no valid membership');
                return false;
            }
        $result = $membership->account->can('viewAny',Membership::class);
        //logger("update authorize",[$membership,$result]);
        logger("viewAny authorizel",[$result]);
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
