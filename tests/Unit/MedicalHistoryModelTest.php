<?php

use App\Models\PatientMedicalHistory;
use App\Models\PatientDentalHistory;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('patient medical history has correct fillable', function () {
    $fillable = [
        'patient_id', 'has_allergy', 'allergy_detail', 'has_systemic_disease',
        'systemic_disease_detail', 'undergoing_treatment', 'treatment_detail',
        'ever_hospitalized', 'hospitalized_reason', 'smoking_or_alcohol'
    ];
    
    $medicalHistory = new PatientMedicalHistory();
    expect($medicalHistory->getFillable())->toEqual($fillable);
    
    expect($medicalHistory->getFillable())
        ->not->toContain('blood_type')
        ->and($medicalHistory->getFillable())->not->toContain('allergies')
        ->and($medicalHistory->getFillable())->not->toContain('current_medications')
        ->and($medicalHistory->getFillable())->not->toContain('medical_conditions');
});

test('patient medical history belongs to patient', function () {
    $patient = Patient::factory()->create();
    $medicalHistory = PatientMedicalHistory::create([
        'patient_id' => $patient->id,
        'has_allergy' => true,
        'allergy_detail' => 'Seafood allergy',
    ]);
    
    expect($medicalHistory->patient)->toBeInstanceOf(Patient::class)
        ->and($medicalHistory->patient->id)->toBe($patient->id);
});

test('patient medical history has only created_at timestamp', function () {
    $medicalHistory = new PatientMedicalHistory();
    
    expect($medicalHistory->timestamps)->toBeFalse();
});

test('patient dental history has correct fillable', function () {
    $fillable = [
        'patient_id', 'frequent_tooth_pain', 'tooth_pain_detail', 'bleeding_gums',
        'ever_dental_treatment', 'dental_treatment_detail', 'brushing_frequency',
        'use_floss_or_mouthwash', 'bad_habits', 'bad_habits_detail',
        'ever_braces', 'braces_years', 'root_canal_treatment', 'root_canal_detail',
        'dentures', 'routine_checkup', 'dental_checkup_frequency', 'doctor_notes'
    ];
    
    $dentalHistory = new PatientDentalHistory();
    expect($dentalHistory->getFillable())->toEqual($fillable);
    
    expect($dentalHistory->getFillable())
        ->not->toContain('last_dental_visit')
        ->and($dentalHistory->getFillable())->not->toContain('dental_problems')
        ->and($dentalHistory->getFillable())->not->toContain('previous_treatments');
});

test('patient dental history belongs to patient', function () {
    $patient = Patient::factory()->create();
    $dentalHistory = PatientDentalHistory::create([
        'patient_id' => $patient->id,
        'frequent_tooth_pain' => false,
        'brushing_frequency' => '2',
    ]);
    
    expect($dentalHistory->patient)->toBeInstanceOf(Patient::class)
        ->and($dentalHistory->patient->id)->toBe($patient->id);
});

test('patient dental history has only created_at timestamp', function () {
    $dentalHistory = new PatientDentalHistory();
    
    expect($dentalHistory->timestamps)->toBeFalse();
});

test('dental history brushing_frequency enum has correct values', function () {
    $patient = Patient::factory()->create();
    $validFrequencies = ['1', '2', 'more_than_2'];
    
    foreach ($validFrequencies as $frequency) {
        $dentalHistory = PatientDentalHistory::create([
            'patient_id' => $patient->id,
            'brushing_frequency' => $frequency,
        ]);
        
        expect($dentalHistory->brushing_frequency)->toBe($frequency);
    }
});
