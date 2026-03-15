<?php

use App\Models\Patient;
use App\Models\PatientMedicalHistory;
use App\Models\PatientDentalHistory;
use App\Models\Reservation;
use App\Models\Rontgen;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('patient has fillable attributes', function () {
    $fillable = [
        'name', 'nickname', 'gender', 'age', 'birth_place', 'birth_date',
        'address', 'village', 'district', 'city', 'phone', 'occupation',
        'parent_name', 'height', 'weight'
    ];
    
    $patient = new Patient();
    expect($patient->getFillable())->toEqual($fillable);
    
    expect($patient->getFillable())->not->toContain('email');
});

test('patient can be created with valid data', function () {
    $patient = Patient::create([
        'name' => 'John Doe',
        'phone' => '081234567890',
        'gender' => 'male',
        'age' => 25,
        'birth_date' => '1999-01-15',
        'address' => 'Jl. Test No. 123',
    ]);
    
    expect($patient)->toBeInstanceOf(Patient::class)
        ->and($patient->name)->toBe('John Doe')
        ->and($patient->phone)->toBe('081234567890')
        ->and($patient->gender)->toBe('male');
});

test('patient has one medical history relationship', function () {
    $patient = Patient::factory()->create();
    $medicalHistory = PatientMedicalHistory::create([
        'patient_id' => $patient->id,
        'has_allergy' => true,
        'allergy_detail' => 'Seafood',
    ]);
    
    expect($patient->medicalHistory)->toBeInstanceOf(PatientMedicalHistory::class)
        ->and($patient->medicalHistory->id)->toBe($medicalHistory->id);
});

test('patient has one dental history relationship', function () {
    $patient = Patient::factory()->create();
    $dentalHistory = PatientDentalHistory::create([
        'patient_id' => $patient->id,
        'frequent_tooth_pain' => true,
        'brushing_frequency' => '2',
    ]);
    
    expect($patient->dentalHistory)->toBeInstanceOf(PatientDentalHistory::class)
        ->and($patient->dentalHistory->id)->toBe($dentalHistory->id);
});

test('patient has many reservations relationship', function () {
    $patient = Patient::factory()->create();
    $doctor = \App\Models\Doctor::factory()->create();
    
    $reservation1 = \App\Models\Reservation::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'complain' => 'Sakit gigi',
        'reservation_date' => now()->addDays(1),
        'appointment_time' => '10:00:00',
        'status' => 'pending',
    ]);
    
    $reservation2 = \App\Models\Reservation::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'complain' => 'Checkup rutin',
        'reservation_date' => now()->addDays(2),
        'appointment_time' => '14:00:00',
        'status' => 'pending',
    ]);
    
    expect($patient->reservations)->toHaveCount(2)
        ->and($patient->reservations->first())->toBeInstanceOf(Reservation::class);
});

test('patient has many rontgen relationship', function () {
    $patient = Patient::factory()->create();
    
    $rontgen = Rontgen::create([
        'patient_id' => $patient->id,
        'xray_image' => 'rontgen_123.jpg',
        'detail' => 'Rontgen panoramic',
    ]);
    
    expect($patient->rontgens)->toHaveCount(1)
        ->and($patient->rontgens->first())->toBeInstanceOf(Rontgen::class);
});

test('patient phone must be unique', function () {
    Patient::factory()->create(['phone' => '081234567890']);
    
    expect(fn() => Patient::create([
        'name' => 'Jane Doe',
        'phone' => '081234567890',
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

test('patient does not have email field in fillable', function () {
    $patient = new Patient();
    
    expect($patient->getFillable())->not->toContain('email');
});
