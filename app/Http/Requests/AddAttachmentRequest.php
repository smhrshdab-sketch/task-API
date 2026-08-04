<?php

namespace App\Http\Requests;

use App\Models\Attachment;
use Illuminate\Foundation\Http\FormRequest;

class AddAttachmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $membership = current_membership();
        if (!$membership) {
            return false;
        }
        //return $membership->account->can('create', Department::class);اسم کلاس را برمی گرداند 
        return $membership->account->can('update', new Attachment());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'model' => ['required','string'],
            'record' => ['required','string'],
            'attachment' => ['required', 'file', 'max:102400', 'mimes:jpg,jpeg,png,pdf,doc,docx,zip,txt,mp3,mp4,mkv,mpeg'],
            'is_public' => ['sometimes', 'boolean'],
        ];
    }
}
