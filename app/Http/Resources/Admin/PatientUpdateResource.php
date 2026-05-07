<?php

namespace App\Http\Resources\Admin;

use Dedoc\Scramble\Attributes\SchemaName;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

#[SchemaName("Admin.PatientUpdateResource")]
class PatientUpdateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
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
        ];
    }
}
