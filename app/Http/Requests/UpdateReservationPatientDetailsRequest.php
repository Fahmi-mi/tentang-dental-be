<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReservationPatientDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $patientId = $this->input('patient_id');

        return [
            'patient_id' => 'required|exists:patients,id',
            'name' => 'required|string|max:150',
            'nickname' => 'nullable|string|max:100',
            'gender' => 'nullable|in:male,female',
            'age' => 'nullable|integer|min:0|max:150',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date|before:today',
            'address' => 'nullable|string',
            'village' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('patients', 'phone')->ignore($patientId),
            ],
            'occupation' => 'nullable|string|max:100',
            'parent_name' => 'nullable|string|max:150',
            'height' => 'nullable|numeric|min:0|max:999.99',
            'weight' => 'nullable|numeric|min:0|max:999.99',

            'medical_history.has_allergy' => 'nullable|boolean',
            'medical_history.allergy_detail' => 'nullable|string',
            'medical_history.has_systemic_disease' => 'nullable|boolean',
            'medical_history.systemic_disease_detail' => 'nullable|string',
            'medical_history.undergoing_treatment' => 'nullable|boolean',
            'medical_history.treatment_detail' => 'nullable|string',
            'medical_history.ever_hospitalized' => 'nullable|boolean',
            'medical_history.hospitalized_reason' => 'nullable|string',
            'medical_history.smoking_or_alcohol' => 'nullable|boolean',

            'dental_history.frequent_tooth_pain' => 'nullable|boolean',
            'dental_history.tooth_pain_detail' => 'nullable|string',
            'dental_history.bleeding_gums' => 'nullable|boolean',
            'dental_history.ever_dental_treatment' => 'nullable|boolean',
            'dental_history.dental_treatment_detail' => 'nullable|string',
            'dental_history.brushing_frequency' => 'nullable|in:1,2,more_than_2',
            'dental_history.use_floss_or_mouthwash' => 'nullable|boolean',
            'dental_history.bad_habits' => 'nullable|boolean',
            'dental_history.bad_habits_detail' => 'nullable|string',
            'dental_history.ever_braces' => 'nullable|boolean',
            'dental_history.braces_years' => 'nullable|integer|min:0',
            'dental_history.root_canal_treatment' => 'nullable|boolean',
            'dental_history.root_canal_detail' => 'nullable|string',
            'dental_history.dentures' => 'nullable|boolean',
            'dental_history.routine_checkup' => 'nullable|boolean',
            'dental_history.dental_checkup_frequency' => 'nullable|in:6_months,1_year,more_than_1_year,never',
            'dental_history.doctor_notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'Patient ID wajib diisi',
            'patient_id.exists' => 'Data pasien tidak ditemukan',
            'name.required' => 'Nama lengkap wajib diisi',
            'phone.required' => 'Nomor telepon wajib diisi',
            'phone.unique' => 'Nomor telepon sudah terdaftar',
        ];
    }
}
