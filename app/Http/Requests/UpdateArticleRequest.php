<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleRequest extends FormRequest
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
            'title' => 'sometimes|required|string|max:200',
            'content' => 'sometimes|required|string',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul artikel wajib diisi',
            'title.max' => 'Judul artikel maksimal 200 karakter',
            'content.required' => 'Konten artikel wajib diisi',
            'image.image' => 'File harus berupa gambar',
            'image.mimes' => 'Format gambar harus jpeg, jpg, png, atau webp',
            'image.max' => 'Ukuran gambar maksimal 2MB',
        ];
    }
}
