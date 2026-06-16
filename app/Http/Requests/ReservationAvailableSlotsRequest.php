<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReservationAvailableSlotsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_id' => 'required|exists:doctors,id',
            'reservation_date' => 'required|date|after_or_equal:today',
        ];
    }

    public function messages(): array
    {
        return [
            'doctor_id.required' => 'Dokter wajib dipilih',
            'doctor_id.exists' => 'Dokter tidak ditemukan',
            'reservation_date.required' => 'Tanggal reservasi wajib dipilih',
            'reservation_date.after_or_equal' => 'Tanggal reservasi tidak boleh di masa lampau',
        ];
    }
}
