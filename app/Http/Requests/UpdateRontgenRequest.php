<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRontgenRequest extends FormRequest
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
            'detail' => 'sometimes|required|string',
            'doctor_id' => 'sometimes|required|exists:doctors,id',
            'images' => 'sometimes|array|min:1',
            'images.*' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
            'tag_ids' => 'sometimes|array',
            'tag_ids.*' => 'exists:tags,id',
            'image_types.*' => 'required|in:xray,profil_gigi,intraoral',
        ];
    }

    public function messages(): array
    {
        return [
            'detail.required' => 'Detail rontgen wajib diisi',
            'doctor_id.required' => 'ID dokter wajib diisi',
            'doctor_id.exists' => 'Data dokter tidak ditemukan',
            'images.array' => 'Gambar pemeriksaan harus berupa array',
            'images.min' => 'Minimal satu gambar pemeriksaan wajib diupload',
            'images.*.required' => 'File gambar pemeriksaan wajib diisi',
            'images.*.image' => 'File harus berupa gambar',
            'images.*.mimes' => 'Format gambar harus jpeg, jpg, png, atau webp',
            'images.*.max' => 'Ukuran gambar pemeriksaan maksimal 5MB',
            'tag_ids.array' => 'Tag harus berupa array',
            'tag_ids.*.exists' => 'Tag tidak ditemukan',
        ];
    }
}
