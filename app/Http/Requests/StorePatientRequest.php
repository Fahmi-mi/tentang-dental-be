<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
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
            'nickname' => 'nullable|string|max:100',
            'gender' => 'required|in:male,female',
            'age' => 'nullable|integer|min:0|max:150',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date|before:today',
            'address' => 'nullable|string',
            'village' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'phone' => 'required|string|max:20|unique:patients,phone',
            'occupation' => 'nullable|string|max:100',
            'parent_name' => 'nullable|string|max:150',
            'height' => 'nullable|numeric|min:0|max:999.99',
            'weight' => 'nullable|numeric|min:0|max:999.99',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi',
            'name.max' => 'Nama maksimal 150 karakter',
            'gender.required' => 'Jenis kelamin wajib dipilih',
            'gender.in' => 'Jenis kelamin harus male atau female',
            'age.integer' => 'Umur harus berupa angka',
            'age.min' => 'Umur tidak valid',
            'age.max' => 'Umur tidak valid',
            'birth_date.date' => 'Format tanggal lahir tidak valid',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini',
            'phone.required' => 'Nomor telepon wajib diisi',
            'phone.unique' => 'Nomor telepon sudah terdaftar',
            'height.numeric' => 'Tinggi badan harus berupa angka',
            'weight.numeric' => 'Berat badan harus berupa angka',
        ];
    }
}
