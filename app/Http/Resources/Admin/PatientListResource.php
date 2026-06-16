<?php

namespace App\Http\Resources\Admin;

use Dedoc\Scramble\Attributes\SchemaName;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

#[SchemaName("Admin.PatientListResource")]
class PatientListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $latestReservation = $this->latestReservation;

        return [
            'id' => $this->id,
            'patient_number' => 'PT-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT),
            'name' => $this->name,
            'phone' => $this->phone,
            'birth_date' => $this->birth_date,
            'gender' => $this->gender,
            'age' => $this->age,
            'latest_reservation_date' => optional($latestReservation?->reservation_date)->format('Y-m-d'),
            'latest_services' => $latestReservation
                ? $latestReservation->services->pluck('name')->values()
                : [],
            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}
