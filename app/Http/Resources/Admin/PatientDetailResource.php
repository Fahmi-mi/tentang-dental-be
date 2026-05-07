<?php

namespace App\Http\Resources\Admin;

use Dedoc\Scramble\Attributes\SchemaName;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

#[SchemaName("Admin.PatientDetailResource")]
class PatientDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $latestReservation = $this->latestReservation;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'nickname' => $this->nickname,
            'phone' => $this->phone,
            'birth_place' => $this->birth_place,
            'birth_date' => $this->birth_date,
            'gender' => $this->gender,
            'address' => $this->address,
            'village' => $this->village,
            'district' => $this->district,
            'city' => $this->city,
            'age' => $this->age,
            'occupation' => $this->occupation,
            'parent_name' => $this->parent_name,
            'height' => $this->height,
            'weight' => $this->weight,
            'medical_history' => $this->medicalHistory ? [
                'id' => $this->medicalHistory->id,
                'has_allergy' => $this->medicalHistory->has_allergy,
                'allergy_detail' => $this->medicalHistory->allergy_detail,
                'has_systemic_disease' => $this->medicalHistory->has_systemic_disease,
                'systemic_disease_detail' => $this->medicalHistory->systemic_disease_detail,
                'undergoing_treatment' => $this->medicalHistory->undergoing_treatment,
                'treatment_detail' => $this->medicalHistory->treatment_detail,
                'ever_hospitalized' => $this->medicalHistory->ever_hospitalized,
                'hospitalized_reason' => $this->medicalHistory->hospitalized_reason,
                'smoking_or_alcohol' => $this->medicalHistory->smoking_or_alcohol,
            ] : null,
            'dental_history' => $this->dentalHistory ? [
                'id' => $this->dentalHistory->id,
                'frequent_tooth_pain' => $this->dentalHistory->frequent_tooth_pain,
                'tooth_pain_detail' => $this->dentalHistory->tooth_pain_detail,
                'bleeding_gums' => $this->dentalHistory->bleeding_gums,
                'ever_dental_treatment' => $this->dentalHistory->ever_dental_treatment,
                'dental_treatment_detail' => $this->dentalHistory->dental_treatment_detail,
                'brushing_frequency' => $this->dentalHistory->brushing_frequency,
                'use_floss_or_mouthwash' => $this->dentalHistory->use_floss_or_mouthwash,
                'bad_habits' => $this->dentalHistory->bad_habits,
                'bad_habits_detail' => $this->dentalHistory->bad_habits_detail,
                'ever_braces' => $this->dentalHistory->ever_braces,
                'braces_years' => $this->dentalHistory->braces_years,
                'root_canal_treatment' => $this->dentalHistory->root_canal_treatment,
                'root_canal_detail' => $this->dentalHistory->root_canal_detail,
                'dentures' => $this->dentalHistory->dentures,
                'routine_checkup' => $this->dentalHistory->routine_checkup,
                'dental_checkup_frequency' => $this->dentalHistory->dental_checkup_frequency,
                'doctor_notes' => $this->dentalHistory->doctor_notes,
            ] : null,
            'last_reservation' => $latestReservation ? [
                'id' => $latestReservation->id,
                'doctor_name' => optional($latestReservation->doctor)->name,
                'reservation_date' => optional($latestReservation->reservation_date)->format('Y-m-d'),
                'appointment_time' => substr((string) $latestReservation->appointment_time, 0, 5),
                'status' => $latestReservation->status,
                'services' => $latestReservation->services->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                    ];
                })->values(),
            ] : null,
            'reservations' => $this->reservations->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'complain' => $reservation->complain,
                    'services' => $reservation->services->map(function ($service) {
                        return [
                            'id' => $service->id,
                            'name' => $service->name,
                        ];
                    })->values(),
                    'doctor_name' => optional($reservation->doctor)->name,
                    'reservation_date' => $reservation->reservation_date,
                    'appointment_time' => substr((string) $reservation->appointment_time, 0, 5),
                    'status' => $reservation->status,
                ];
            })->values(),
            'rontgens' => $this->rontgens->map(function ($rontgen) {
                return [
                    'id' => $rontgen->id,
                    'doctor_id' => $rontgen->doctor_id,
                    'latest_image_url' => $rontgen->latest_image_url,
                    'detail' => $rontgen->detail,
                    'created_at' => optional($rontgen->created_at)->format('Y-m-d H:i:s'),
                ];
            })->values(),
            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)->format('Y-m-d H:i:s'),
        ];
    }
}
