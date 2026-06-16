<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends FormRequest
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
        $patientId = $this->route('id');

        return [
            'name' => 'sometimes|required|string|max:150',
            'nickname' => 'nullable|string|max:100',
            'gender' => 'sometimes|required|in:male,female',
            'age' => 'nullable|integer|min:0|max:150',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date|before:today',
            'address' => 'nullable|string',
            'village' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'phone' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('patients', 'phone')->ignore($patientId)
            ],
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
            'gender.required' => 'Jenis kelamin wajib dipilih',
            'gender.in' => 'Jenis kelamin harus male atau female',
            'phone.required' => 'Nomor telepon wajib diisi',
            'phone.unique' => 'Nomor telepon sudah terdaftar',
        ];
    }
}
