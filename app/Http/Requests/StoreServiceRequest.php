<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
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
            'name' => 'required|string|max:150',
            'detail' => 'required|string',
            'icon' => 'required|image|mimes:jpeg,jpg,png,webp,svg|max:1024',
            'article_content' => 'required|string',
            'support_image' => 'required|image|mimes:jpeg,jpg,png,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama layanan wajib diisi',
            'name.max' => 'Nama layanan maksimal 150 karakter',
            'detail.required' => 'Detail singkat layanan wajib diisi',
            'icon.required' => 'Icon layanan wajib diupload',
            'icon.image' => 'Icon harus berupa gambar',
            'icon.mimes' => 'Format icon harus jpeg, jpg, png, webp, atau svg',
            'icon.max' => 'Ukuran icon maksimal 1MB',
            'article_content.required' => 'Konten artikel wajib diisi',
            'support_image.required' => 'Gambar pendukung wajib diupload',
            'support_image.image' => 'File harus berupa gambar',
            'support_image.mimes' => 'Format gambar harus jpeg, jpg, png, atau webp',
            'support_image.max' => 'Ukuran gambar maksimal 2MB',
        ];
    }
}
