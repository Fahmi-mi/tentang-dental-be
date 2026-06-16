<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGalleryRequest extends FormRequest
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
        return [
            'image' => 'required|image|mimes:jpeg,jpg,png,webp|max:2048',
            'caption' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'Gambar galeri wajib diupload',
            'image.image' => 'File harus berupa gambar',
            'image.mimes' => 'Format gambar harus jpeg, jpg, png, atau webp',
            'image.max' => 'Ukuran gambar maksimal 2MB',
            'caption.max' => 'Caption maksimal 255 karakter',
        ];
    }
}
