<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminChangeEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => 'required|string|min:8',
            'new_email' => 'required|email|max:150|different:email|unique:admins,email',
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Password saat ini wajib diisi',
            'current_password.min' => 'Password saat ini minimal 8 karakter',
            'new_email.required' => 'Email baru wajib diisi',
            'new_email.email' => 'Format email baru tidak valid',
            'new_email.unique' => 'Email baru sudah digunakan',
            'new_email.different' => 'Email baru harus berbeda dari email lama',
        ];
    }
}
