<?php

namespace App\Http\Requests;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool{
        $membership = current_membership();        
        if(!$membership){
            logger("No valid membership");
            return false;
        }
        $result = $membership->account->can('update', new Account());
        //logger("update authorize",[$membership,$result]);
        logger("update authorize",[$result]);
        return $result;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array{
        $account = $this->route('account');
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 
                'email',
                // Ignore current account's email when checking uniqueness
                Rule::unique('accounts')->ignore($account?->id)//برداشتن موقت محدودیت یکتا بودن ایمیل چون ممکنه کاربر ایمیل رو عوض  نکنه
                //$account?->id : If $account is null → returns null. If $account exists → returns $account->id. This prevents errors when account doesn't exist
            ],
            'password' => ['nullable', 'string', 'min:8'], // Make password optional for update
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'], // 2MB max
            'address' => 'nullable|string',
            'phone' => 'nullable|string'
        ];
    }
    
    
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.unique' => 'This email is already registered',
            'password.min' => 'Password must be at least 8 characters',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
        ];
    }
    
    protected function prepareForValidation(): void{
        // Only hash password if provided
        // if ($this->has('password') && $this->password) {
        //     $this->merge([
        //         'password' => bcrypt($this->password)
        //     ]);
        // } else {
        //     // Remove password from validation if not provided
        //     $this->request->remove('password');
        // }
    }
}
