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
        return true; // Izinkan user yang terautentikasi untuk membuat request ini
    }

    public function rules(): array
    {
        $userId = Auth::id(); // Dapatkan ID user yang sedang login

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId), // Email harus unik, kecuali untuk user ini sendiri
            ],
            'current_password' => ['nullable', 'string', 'current_password'], // Hanya jika ingin ubah password
            'password' => ['nullable', 'string', 'confirmed', Password::defaults(), 'required_with:current_password'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'], // Maks 2MB
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