<?php

namespace App\Http\Resources\Admin;

use Dedoc\Scramble\Attributes\SchemaName;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

#[SchemaName("Admin.RontgenDetailResource")]
class RontgenDetailResource extends JsonResource
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
                    'doctor_notes' => $dentalHistory->doctor_notes,
                ] : null,
            ],
            'doctor' => [
                'id' => optional($this->doctor)->id,
                'name' => optional($this->doctor)->name,
                'specialization' => optional($this->doctor)->specialization,
            ],
            'latest_image_url' => $this->latest_image_url,
            'examination_images' => $this->examinationImages->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image_url' => $this->toPublicImageUrl($image->image_path),
                    'image_type' => $image->image_type,
                    'created_at' => optional($image->created_at)->format('Y-m-d H:i:s'),
                ];
            })->values(),
            'tags' => $this->tags->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'tag_name' => $tag->tag_name,
                    'detail' => $tag->detail,
                ];
            })->values(),
            'detail' => $this->detail,
            'status' => $this->status,
            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)->format('Y-m-d H:i:s'),
        ];
    }

    private function toPublicImageUrl(?string $fileName): ?string
    {
        if (!$fileName) {
            return null;
        }

        if (Storage::disk('public')->exists('rontgen/' . $fileName)) {
            return asset('storage/rontgen/' . $fileName);
        }

        if (Storage::disk('public')->exists('rontgens/' . $fileName)) {
            return asset('storage/rontgens/' . $fileName);
        }

        return null;
    }
}
