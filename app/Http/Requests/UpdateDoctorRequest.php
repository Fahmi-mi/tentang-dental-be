<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:150',
            'specialization' => 'nullable|string|max:150',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'schedule' => 'sometimes|required|json',
            'statement' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama dokter wajib diisi',
            'name.max' => 'Nama dokter maksimal 150 karakter',
            'photo.image' => 'File harus berupa gambar',
            'photo.mimes' => 'Format gambar harus jpeg, jpg, png, atau webp',
            'photo.max' => 'Ukuran foto maksimal 2MB',
            'schedule.required' => 'Jadwal praktek wajib diisi',
            'schedule.json' => 'Format jadwal harus JSON valid',
        ];
    }
}
