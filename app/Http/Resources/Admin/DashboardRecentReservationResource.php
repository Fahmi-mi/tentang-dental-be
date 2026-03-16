<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardRecentReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_name' => optional($this->patient)->name,
            'service_name' => optional($this->services->first())->name,
            'doctor_name' => optional($this->doctor)->name,
            'reservation_date' => $this->reservation_date,
            'appointment_time' => substr((string) $this->appointment_time, 0, 5),
            'status' => $this->status,
        ];
    }
}
