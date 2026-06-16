<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestimonialRequest extends FormRequest
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
            'rating' => 'required|integer|min:1|max:5',
            'testimoni' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama pemberi testimoni wajib diisi',
            'name.max' => 'Nama maksimal 150 karakter',
            'rating.required' => 'Rating wajib dipilih',
            'rating.integer' => 'Rating harus berupa angka',
            'rating.min' => 'Rating minimal 1',
            'rating.max' => 'Rating maksimal 5',
            'testimoni.required' => 'Isi testimoni wajib diisi',
            'photo.image' => 'File harus berupa gambar',
            'photo.mimes' => 'Format gambar harus jpeg, jpg, png, atau webp',
            'photo.max' => 'Ukuran foto maksimal 2MB',
        ];
    }
}
