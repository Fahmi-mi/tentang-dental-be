<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient' => [
                'id' => optional($this->patient)->id,
                'name' => optional($this->patient)->name,
                'phone' => optional($this->patient)->phone,
            ],
            'services' => $this->services->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                ];
            })->values(),
            'doctor' => [
                'id' => optional($this->doctor)->id,
                'name' => optional($this->doctor)->name,
            ],
            'complain' => $this->complain,
            'reservation_date' => $this->reservation_date,
            'appointment_time' => substr((string) $this->appointment_time, 0, 5),
            'birth_date' => $this->birth_date,
            'age' => $this->age,
            'patient_category' => $this->patient_category,
            'status' => $this->status,
            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}
