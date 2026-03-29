<?php

use App\Models\Admin;
use App\Models\Patient;
use App\Models\Reservation;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\PatientMedicalHistory;
use App\Models\PatientDentalHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = Admin::factory()->create(['role' => 'registration']);
    Sanctum::actingAs($this->admin);
});

test('admin can get list of patients without email field', function () {
    Patient::factory()->count(3)->create();
    
    $response = $this->getJson('/api/admin/patients');
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'patients' => [
                    '*' => ['id', 'name', 'phone', 'gender', 'age', 'birth_date']
                ],
                'pagination'
            ]
        ]);
    
    $response->assertJsonMissing(['email']);
});

test('admin can get patient detail with services plural relationship', function () {
    $patient = Patient::factory()->create(['name' => 'John Doe']);
    $doctor = Doctor::factory()->create();
    $service1 = Service::factory()->create(['name' => 'Scaling']);
    $service2 = Service::factory()->create(['name' => 'Bleaching']);
    
    $reservation = Reservation::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'complain' => 'Test',
        'reservation_date' => now(),
        'appointment_time' => '10:00:00',
        'status' => 'pending',
    ]);
    $reservation->services()->attach([$service1->id, $service2->id]);
    
    $response = $this->getJson("/api/admin/patients/{$patient->id}");
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id', 'name', 'phone',
                'reservations' => [
                    '*' => [
                        'id', 'complain', 'status',
                        'services' => [
                            '*' => ['id', 'name']
                        ]
                    ]
                ]
            ]
        ]);
});

test('admin can update patient with birth_date field', function () {
    $patient = Patient::factory()->create();
    
    $response = $this->putJson("/api/admin/patients/{$patient->id}", [
        'name' => 'Updated Name',
        'phone' => $patient->phone,
        'birth_date' => '1990-01-15',
        'age' => 36,
    ]);
    
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'Updated Name',
            ]
        ]);
});

test('admin can delete patient', function () {
    $patient = Patient::factory()->create();
    
    $response = $this->deleteJson("/api/admin/patients/{$patient->id}");
    
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Data pasien berhasil dihapus'
        ]);
    
    $this->assertDatabaseMissing('patients', ['id' => $patient->id]);
});

test('admin can get list of reservations', function () {
    $patient = Patient::factory()->create();
    $doctor = Doctor::factory()->create();
    Reservation::factory()->count(3)->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
    ]);
    
    $response = $this->getJson('/api/admin/reservations');
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'reservations' => [
                    '*' => ['id', 'patient', 'doctor', 'complain', 'reservation_date', 'appointment_time', 'status']
                ],
                'pagination'
            ]
        ]);
});

test('reservation detail shows services array not single service', function () {
    $patient = Patient::factory()->create();
    $doctor = Doctor::factory()->create();
    $service1 = Service::factory()->create(['name' => 'Service 1']);
    $service2 = Service::factory()->create(['name' => 'Service 2']);
    
    $reservation = Reservation::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'complain' => 'Test',
        'reservation_date' => now(),
        'appointment_time' => '10:00:00',
        'status' => 'pending',
    ]);
    $reservation->services()->attach([$service1->id, $service2->id]);
    
    $response = $this->getJson("/api/admin/reservations/{$reservation->id}");
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'services' => [
                    '*' => ['id', 'name']
                ]
            ]
        ]);
    
    $response->assertJsonMissing(['notes', 'email', 'updated_at']);
});

test('admin can update reservation status', function () {
    $patient = Patient::factory()->create();
    $doctor = Doctor::factory()->create();
    $reservation = Reservation::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'complain' => 'Test',
        'reservation_date' => now(),
        'appointment_time' => '10:00:00',
        'status' => 'pending',
    ]);
    
    $response = $this->putJson("/api/admin/reservations/{$reservation->id}", [
        'status' => 'validated'
    ]);
    
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'status' => 'validated'
            ]
        ]);
});

test('admin can delete reservation', function () {
    $patient = Patient::factory()->create();
    $doctor = Doctor::factory()->create();
    $reservation = Reservation::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'complain' => 'Test',
        'reservation_date' => now(),
        'appointment_time' => '10:00:00',
        'status' => 'pending',
    ]);
    
    $response = $this->deleteJson("/api/admin/reservations/{$reservation->id}");
    
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Reservasi berhasil dihapus'
        ]);
});

test('reservation response has appointment_time not reservation_time', function () {
    $patient = Patient::factory()->create();
    $doctor = Doctor::factory()->create();
    $reservation = Reservation::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'complain' => 'Test',
        'reservation_date' => now(),
        'appointment_time' => '10:00:00',
        'status' => 'pending',
    ]);
    
    $response = $this->getJson("/api/admin/reservations/{$reservation->id}");
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['appointment_time']
        ]);
});

test('patient detail shows medical history', function () {
    $patient = Patient::factory()->create();
    PatientMedicalHistory::create([
        'patient_id' => $patient->id,
        'has_allergy' => true,
        'allergy_detail' => 'Seafood',
        'has_systemic_disease' => false,
        'undergoing_treatment' => false,
        'ever_hospitalized' => false,
        'smoking_or_alcohol' => false,
    ]);
    
    $response = $this->getJson("/api/admin/patients/{$patient->id}");
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'medical_history' => [
                    'has_allergy',
                    'allergy_detail',
                    'has_systemic_disease',
                    'undergoing_treatment',
                    'ever_hospitalized',
                    'smoking_or_alcohol'
                ]
            ]
        ]);
    
    $response->assertJsonMissing(['blood_type', 'current_medications']);
});

test('patient detail shows dental history', function () {
    $patient = Patient::factory()->create();
    PatientDentalHistory::create([
        'patient_id' => $patient->id,
        'frequent_tooth_pain' => false,
        'bleeding_gums' => false,
        'brushing_frequency' => '2',
        'use_floss_or_mouthwash' => true,
        'dental_checkup_frequency' => '6_months',
    ]);
    
    $response = $this->getJson("/api/admin/patients/{$patient->id}");
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'dental_history' => [
                    'frequent_tooth_pain',
                    'bleeding_gums',
                    'brushing_frequency',
                    'dental_checkup_frequency'
                ]
            ]
        ]);
    
    $response->assertJsonMissing(['last_dental_visit', 'dental_problems']);
});
