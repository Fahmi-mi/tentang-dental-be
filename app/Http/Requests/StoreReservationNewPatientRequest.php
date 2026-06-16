<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationNewPatientRequest extends FormRequest
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
            // Patient data
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

            // Medical History
            'has_allergy' => 'required|boolean',
            'allergy_detail' => 'required_if:has_allergy,true|nullable|string',
            'has_systemic_disease' => 'required|boolean',
            'systemic_disease_detail' => 'required_if:has_systemic_disease,true|nullable|string',
            'undergoing_treatment' => 'required|boolean',
            'treatment_detail' => 'required_if:undergoing_treatment,true|nullable|string',
            'ever_hospitalized' => 'required|boolean',
            'hospitalized_reason' => 'required_if:ever_hospitalized,true|nullable|string',
            'smoking_or_alcohol' => 'required|boolean',

            // Dental History
            'frequent_tooth_pain' => 'required|boolean',
            'tooth_pain_detail' => 'required_if:frequent_tooth_pain,true|nullable|string',
            'bleeding_gums' => 'required|boolean',
            'ever_dental_treatment' => 'required|boolean',
            'dental_treatment_detail' => 'required_if:ever_dental_treatment,true|nullable|string',
            'brushing_frequency' => 'required|in:1,2,more_than_2',
            'use_floss_or_mouthwash' => 'required|boolean',
            'bad_habits' => 'required|boolean',
            'bad_habits_detail' => 'required_if:bad_habits,true|nullable|string',
            'ever_braces' => 'required|boolean',
            'braces_years' => 'required_if:ever_braces,true|nullable|integer|min:0',
            'root_canal_treatment' => 'required|boolean',
            'root_canal_detail' => 'required_if:root_canal_treatment,true|nullable|string',
            'dentures' => 'required|boolean',
            'routine_checkup' => 'required|boolean',
            'dental_checkup_frequency' => 'required|in:6_months,1_year,more_than_1_year,never',

            // Reservation data
            'doctor_id' => 'required|exists:doctors,id',
            'complain' => 'required|string',
            'reservation_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'service_ids' => 'required|array|min:1|max:3',
            'service_ids.*' => 'exists:services,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi',
            'gender.required' => 'Jenis kelamin wajib dipilih',
            'phone.required' => 'Nomor telepon wajib diisi',
            'phone.unique' => 'Nomor telepon sudah terdaftar',
            'doctor_id.required' => 'Dokter wajib dipilih',
            'doctor_id.exists' => 'Dokter tidak ditemukan',
            'complain.required' => 'Keluhan wajib diisi',
            'reservation_date.required' => 'Tanggal reservasi wajib dipilih',
            'reservation_date.after_or_equal' => 'Tanggal reservasi tidak boleh sebelum hari ini',
            'appointment_time.required' => 'Jam kedatangan wajib dipilih',
            'appointment_time.date_format' => 'Format jam tidak valid (HH:MM)',
            'service_ids.required' => 'Minimal pilih 1 layanan',
            'service_ids.max' => 'Maksimal 3 layanan per reservasi',
            'service_ids.*.exists' => 'Layanan tidak ditemukan',
        ];
    }
}
