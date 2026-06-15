<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->has("patient_id") && is_string($this->patient_id)) {
            $patientId = strtoupper($this->patient_id);
            if (str_starts_with($patientId, "AA")) {
                $this->merge([
                    "patient_id" => substr($this->patient_id, 2),
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            "patient_category" => "required|in:new,existing",
            "patient_id" =>
                "nullable|required_if:patient_category,existing|exists:patients,id",
            "name" => "required|string|max:150",
            "phone" => "required|string|max:20",
            "gender" => "nullable|in:male,female",
            "address" => "nullable|string",
            "birth_date" => "nullable|date|before:today",
            "age" => "nullable|integer|min:0|max:150",

            "doctor_id" => "required|exists:doctors,id",
            "complain" => "required|string",
            "reservation_date" => "required|date|after_or_equal:today",
            "appointment_time" => "required|date_format:H:i",
            "service_ids" => "required|array|min:1|max:3",
            "service_ids.*" => "required|exists:services,id|distinct",
        ];
    }

    public function messages(): array
    {
        return [
            "patient_category.required" => "Kategori pasien wajib dipilih",
            "patient_category.in" => "Kategori pasien harus new atau existing",
            "patient_id.required_if" =>
                "Nomor pasien wajib diisi untuk pasien lama",
            "patient_id.exists" => "Nomor pasien tidak ditemukan",
            "name.required" => "Nama lengkap wajib diisi",
            "phone.required" => "Nomor telepon wajib diisi",
            "doctor_id.required" => "Dokter wajib dipilih",
            "doctor_id.exists" => "Dokter tidak ditemukan",
            "complain.required" => "Keluhan wajib diisi",
            "reservation_date.required" => "Tanggal reservasi wajib dipilih",
            "reservation_date.after_or_equal" =>
                "Tanggal reservasi tidak boleh di masa lampau",
            "appointment_time.required" => "Jam reservasi wajib diisi",
            "appointment_time.date_format" =>
                "Format jam reservasi harus HH:MM",
            "service_ids.required" => "Minimal pilih 1 layanan",
            "service_ids.max" => "Maksimal 3 layanan per reservasi",
            "service_ids.*.exists" => "Layanan tidak ditemukan",
            "service_ids.*.distinct" => "Layanan tidak boleh duplikat",
        ];
    }
}
