<?php

namespace App\Http\Resources\Admin;

use Dedoc\Scramble\Attributes\SchemaName;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

#[SchemaName("Admin.ReservationListResource")]
class ReservationListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "patient" => [
                "id" => optional($this->patient)->id,
                "patient_number" => optional($this->patient)
                    ? "AA" . str_pad($this->patient->id, 5, "0", STR_PAD_LEFT)
                    : null,
                "name" => optional($this->patient)->name,
                "phone" => optional($this->patient)->phone,
            ],
            "services" => $this->services
                ->map(function ($service) {
                    return [
                        "id" => $service->id,
                        "name" => $service->name,
                    ];
                })
                ->values(),
            "doctor" => [
                "id" => optional($this->doctor)->id,
                "name" => optional($this->doctor)->name,
            ],
            "complain" => $this->complain,
            "reservation_date" => $this->reservation_date,
            "appointment_time" => $this->normalizeTime($this->appointment_time),
            "birth_date" => $this->birth_date,
            "age" => $this->age,
            "patient_category" => $this->patient_category,
            "status" => $this->status,
            "created_at" => optional($this->created_at)->format("Y-m-d H:i:s"),
        ];
    }

    private function normalizeTime($value): ?string
    {
        if (!$value) {
            return null;
        }

        $text = (string) $value;

        if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $text)) {
            return substr($text, 0, 5);
        }

        try {
            return \Carbon\Carbon::parse($text)->format("H:i");
        } catch (\Throwable $e) {
            return null;
        }
    }
}
