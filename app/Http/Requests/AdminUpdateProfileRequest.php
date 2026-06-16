<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:100',
            'profile_image' => 'sometimes|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'Nama maksimal 100 karakter',
            'profile_image.image' => 'File profile image harus berupa gambar',
            'profile_image.mimes' => 'Format profile image harus jpg, jpeg, png, atau webp',
            'profile_image.max' => 'Ukuran profile image maksimal 2MB',
        ];
    }
}
