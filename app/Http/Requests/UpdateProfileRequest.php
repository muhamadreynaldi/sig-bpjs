<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = Auth::id();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId),
            ],
            'current_password' => ['nullable', 'string', 'current_password'],
            'password' => ['nullable', 'string', 'confirmed', Password::defaults(), 'required_with:current_password'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.current_password' => 'Password saat ini tidak cocok.',
            'password.required_with' => 'Password baru diperlukan jika Anda mengisi password saat ini.',
        ];
    }
}