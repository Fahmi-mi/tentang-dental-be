<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationExistingPatientRequest extends FormRequest
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
            'patient_id' => 'required|exists:patients,id',
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
            'patient_id.required' => 'ID pasien wajib diisi',
            'patient_id.exists' => 'Data pasien tidak ditemukan',
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
