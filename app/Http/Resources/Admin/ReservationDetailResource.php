<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $medicalHistory = optional($this->patient)->medicalHistory;
        $dentalHistory = optional($this->patient)->dentalHistory;

        return [
            'id' => $this->id,
            'patient' => [
                'id' => optional($this->patient)->id,
                'name' => optional($this->patient)->name,
                'phone' => optional($this->patient)->phone,
                'birth_date' => optional($this->patient)->birth_date,
                'gender' => optional($this->patient)->gender,
                'address' => optional($this->patient)->address,
                'medical_history' => $medicalHistory ? [
                    'has_allergy' => $medicalHistory->has_allergy,
                    'allergy_detail' => $medicalHistory->allergy_detail,
                    'has_systemic_disease' => $medicalHistory->has_systemic_disease,
                    'systemic_disease_detail' => $medicalHistory->systemic_disease_detail,
                    'undergoing_treatment' => $medicalHistory->undergoing_treatment,
                    'treatment_detail' => $medicalHistory->treatment_detail,
                    'ever_hospitalized' => $medicalHistory->ever_hospitalized,
                    'hospitalized_reason' => $medicalHistory->hospitalized_reason,
                    'smoking_or_alcohol' => $medicalHistory->smoking_or_alcohol,
                ] : null,
                'dental_history' => $dentalHistory ? [
                    'frequent_tooth_pain' => $dentalHistory->frequent_tooth_pain,
                    'tooth_pain_detail' => $dentalHistory->tooth_pain_detail,
                    'bleeding_gums' => $dentalHistory->bleeding_gums,
                    'ever_dental_treatment' => $dentalHistory->ever_dental_treatment,
                    'dental_treatment_detail' => $dentalHistory->dental_treatment_detail,
                    'brushing_frequency' => $dentalHistory->brushing_frequency,
                    'use_floss_or_mouthwash' => $dentalHistory->use_floss_or_mouthwash,
                    'bad_habits' => $dentalHistory->bad_habits,
                    'bad_habits_detail' => $dentalHistory->bad_habits_detail,
                    'ever_braces' => $dentalHistory->ever_braces,
                    'braces_years' => $dentalHistory->braces_years,
                    'root_canal_treatment' => $dentalHistory->root_canal_treatment,
                    'root_canal_detail' => $dentalHistory->root_canal_detail,
                    'dentures' => $dentalHistory->dentures,
                    'routine_checkup' => $dentalHistory->routine_checkup,
                    'dental_checkup_frequency' => $dentalHistory->dental_checkup_frequency,
                ] : null,
            ],
            'patient_form' => [
                'patient_id' => optional($this->patient)->id,
                'name' => optional($this->patient)->name,
                'nickname' => optional($this->patient)->nickname,
                'gender' => optional($this->patient)->gender,
                'age' => optional($this->patient)->age,
                'birth_place' => optional($this->patient)->birth_place,
                'birth_date' => optional($this->patient)->birth_date,
                'address' => optional($this->patient)->address,
                'village' => optional($this->patient)->village,
                'district' => optional($this->patient)->district,
                'city' => optional($this->patient)->city,
                'phone' => optional($this->patient)->phone,
                'occupation' => optional($this->patient)->occupation,
                'parent_name' => optional($this->patient)->parent_name,
                'height' => optional($this->patient)->height,
                'weight' => optional($this->patient)->weight,
            ],
            'medical_history_form' => [
                'has_allergy' => optional($medicalHistory)->has_allergy,
                'allergy_detail' => optional($medicalHistory)->allergy_detail,
                'has_systemic_disease' => optional($medicalHistory)->has_systemic_disease,
                'systemic_disease_detail' => optional($medicalHistory)->systemic_disease_detail,
                'undergoing_treatment' => optional($medicalHistory)->undergoing_treatment,
                'treatment_detail' => optional($medicalHistory)->treatment_detail,
                'ever_hospitalized' => optional($medicalHistory)->ever_hospitalized,
                'hospitalized_reason' => optional($medicalHistory)->hospitalized_reason,
                'smoking_or_alcohol' => optional($medicalHistory)->smoking_or_alcohol,
            ],
            'dental_history_form' => [
                'frequent_tooth_pain' => optional($dentalHistory)->frequent_tooth_pain,
                'tooth_pain_detail' => optional($dentalHistory)->tooth_pain_detail,
                'bleeding_gums' => optional($dentalHistory)->bleeding_gums,
                'ever_dental_treatment' => optional($dentalHistory)->ever_dental_treatment,
                'dental_treatment_detail' => optional($dentalHistory)->dental_treatment_detail,
                'brushing_frequency' => optional($dentalHistory)->brushing_frequency,
                'use_floss_or_mouthwash' => optional($dentalHistory)->use_floss_or_mouthwash,
                'bad_habits' => optional($dentalHistory)->bad_habits,
                'bad_habits_detail' => optional($dentalHistory)->bad_habits_detail,
                'ever_braces' => optional($dentalHistory)->ever_braces,
                'braces_years' => optional($dentalHistory)->braces_years,
                'root_canal_treatment' => optional($dentalHistory)->root_canal_treatment,
                'root_canal_detail' => optional($dentalHistory)->root_canal_detail,
                'dentures' => optional($dentalHistory)->dentures,
                'routine_checkup' => optional($dentalHistory)->routine_checkup,
                'dental_checkup_frequency' => optional($dentalHistory)->dental_checkup_frequency,
                'doctor_notes' => optional($dentalHistory)->doctor_notes,
            ],
            'services' => $this->services->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'detail' => $service->detail,
                ];
            })->values(),
            'doctor' => [
                'id' => optional($this->doctor)->id,
                'name' => optional($this->doctor)->name,
                'specialization' => optional($this->doctor)->specialization,
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
