<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = Auth::user()->id;

        return [
            'first_name' => 'required|string|min:3|max:20',
            'last_name' => 'required|string|min:3|max:20',
            'username' => [
                'nullable',
                'string',
                'min:3',
                'max:15',
                Rule::unique('users', 'username')->ignore($id)
            ],
            'email' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'email')->ignore($id)
            ],
            'password' => 'sometimes|nullable|min:6|max:18',
            'bio' => 'sometimes|nullable|min:3|max:100',
            'avatar' => 'nullable|image|mimes:jpeg,png,gif|max:10240'
        ];
    }
}
