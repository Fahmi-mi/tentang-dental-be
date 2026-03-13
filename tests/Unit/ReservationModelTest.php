<?php

use App\Models\Reservation;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('reservation has correct fillable attributes according to BACKEND_PLAN', function () {
    $fillable = [
        'patient_id', 'doctor_id', 'complain', 'reservation_date',
        'appointment_time', 'status'
    ];
    
    $reservation = new Reservation();
    expect($reservation->getFillable())->toEqual($fillable);
    
    expect($reservation->getFillable())
        ->not->toContain('notes')
        ->and($reservation->getFillable())->not->toContain('email');
});

test('reservation belongs to patient', function () {
    $patient = Patient::factory()->create();
    $doctor = Doctor::factory()->create();
    $reservation = Reservation::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'complain' => 'Sakit gigi',
        'reservation_date' => now()->addDays(1),
        'appointment_time' => '10:00:00',
        'status' => 'pending',
    ]);
    
    expect($reservation->patient)->toBeInstanceOf(Patient::class)
        ->and($reservation->patient->id)->toBe($patient->id);
});

test('reservation belongs to doctor', function () {
    $patient = Patient::factory()->create();
    $doctor = Doctor::factory()->create();
    $reservation = Reservation::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'complain' => 'Sakit gigi',
        'reservation_date' => now()->addDays(1),
        'appointment_time' => '10:00:00',
        'status' => 'pending',
    ]);
    
    expect($reservation->doctor)->toBeInstanceOf(Doctor::class)
        ->and($reservation->doctor->id)->toBe($doctor->id);
});

test('reservation belongs to many services through pivot table', function () {
    $patient = Patient::factory()->create();
    $doctor = Doctor::factory()->create();
    $reservation = Reservation::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'complain' => 'Sakit gigi',
        'reservation_date' => now()->addDays(1),
        'appointment_time' => '10:00:00',
        'status' => 'pending',
    ]);
    
    $service1 = Service::factory()->create(['name' => 'Scaling']);
    $service2 = Service::factory()->create(['name' => 'Bleaching']);
    $service3 = Service::factory()->create(['name' => 'Cabut Gigi']);
    
    $reservation->services()->attach([$service1->id, $service2->id, $service3->id]);
    
    expect($reservation->services)->toHaveCount(3)
        ->and($reservation->services->first())->toBeInstanceOf(Service::class);
});

test('reservation has only created_at timestamp according to BACKEND_PLAN', function () {
    $reservation = new Reservation();
    
    expect($reservation->timestamps)->toBeFalse();
});

test('reservation status enum values are correct', function () {
    $patient = Patient::factory()->create();
    $doctor = Doctor::factory()->create();
    
    $validStatuses = ['pending', 'validated', 'completed', 'cancelled'];
    
    foreach ($validStatuses as $status) {
        $reservation = Reservation::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'complain' => 'Test',
            'reservation_date' => now()->addDays(1),
            'appointment_time' => '10:00:00',
            'status' => $status,
        ]);
        
        expect($reservation->status)->toBe($status);
    }
});

test('reservation has appointment_time field as per BACKEND_PLAN', function () {
    $reservation = new Reservation();
    
    expect($reservation->getFillable())->toContain('appointment_time');
});
