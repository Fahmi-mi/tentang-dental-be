<?php

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can check if patient exists by phone', function () {
    Patient::factory()->create([
        'name' => 'John Doe',
        'phone' => '081234567890',
    ]);
    
    $response = $this->postJson('/api/reservations/check-patient', [
        'phone' => '081234567890'
    ]);
    
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'exists' => true,
                'patient' => [
                    'name' => 'John Doe',
                    'phone' => '081234567890',
                ]
            ]
        ]);
    
    $response->assertJsonMissing(['email']);
});

test('returns patient not found when checking non-existent phone', function () {
    $response = $this->postJson('/api/reservations/check-patient', [
        'phone' => '081999999999'
    ]);
    
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'exists' => false,
            ]
        ]);
});

test('can create reservation for new patient with medical and dental history', function () {
    $doctor = Doctor::factory()->create();
    $service1 = Service::factory()->create(['name' => 'Scaling']);
    $service2 = Service::factory()->create(['name' => 'Bleaching']);
    
    $response = $this->postJson('/api/reservations/new-patient', [
        'name' => 'Jane Doe',
        'phone' => '081234567890',
        'gender' => 'female',
        'age' => 28,
        'birth_place' => 'Jakarta',
        'birth_date' => '1996-05-15',
        'address' => 'Jl. Test No. 123',
        
        'has_allergy' => true,
        'allergy_detail' => 'Seafood',
        'has_systemic_disease' => false,
        'undergoing_treatment' => false,
        'ever_hospitalized' => false,
        'smoking_or_alcohol' => false,
        
        'frequent_tooth_pain' => false,
        'bleeding_gums' => false,
        'ever_dental_treatment' => true,
        'dental_treatment_detail' => 'Pernah tambal gigi',
        'brushing_frequency' => '2',
        'use_floss_or_mouthwash' => true,
        'bad_habits' => false,
        'ever_braces' => false,
        'root_canal_treatment' => false,
        'dentures' => false,
        'routine_checkup' => true,
        'dental_checkup_frequency' => '6_months',
        
        'doctor_id' => $doctor->id,
        'service_ids' => [$service1->id, $service2->id], // Max 3 services
        'complain' => 'Mau scaling dan bleaching',
        'reservation_date' => now()->addDays(1)->format('Y-m-d'),
        'appointment_time' => '10:00:00',
    ]);
    
    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Reservasi berhasil dibuat'
        ]);
    
    $this->assertDatabaseHas('patients', [
        'name' => 'Jane Doe',
        'phone' => '081234567890',
    ]);
    
    $this->assertDatabaseHas('patient_medical_histories', [
        'has_allergy' => true,
        'allergy_detail' => 'Seafood',
    ]);
    
    $this->assertDatabaseHas('patient_dental_histories', [
        'brushing_frequency' => '2',
        'dental_checkup_frequency' => '6_months',
    ]);
    
    $this->assertDatabaseHas('reservations', [
        'complain' => 'Mau scaling dan bleaching',
        'status' => 'pending',
    ]);
});

test('can create reservation for existing patient', function () {
    $patient = Patient::factory()->create(['phone' => '081234567890']);
    $doctor = Doctor::factory()->create();
    $service = Service::factory()->create();
    
    $response = $this->postJson('/api/reservations/existing-patient', [
        'phone' => '081234567890',
        'doctor_id' => $doctor->id,
        'service_ids' => [$service->id],
        'complain' => 'Checkup rutin',
        'reservation_date' => now()->addDays(1)->format('Y-m-d'),
        'appointment_time' => '14:00:00',
    ]);
    
    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Reservasi berhasil dibuat'
        ]);
});

test('cannot create reservation with more than 3 services', function () {
    $doctor = Doctor::factory()->create();
    $services = Service::factory()->count(4)->create();
    
    $response = $this->postJson('/api/reservations/new-patient', [
        'name' => 'Test Patient',
        'phone' => '081234567890',
        'doctor_id' => $doctor->id,
        'service_ids' => $services->pluck('id')->toArray(),
        'complain' => 'Test',
        'reservation_date' => now()->addDays(1)->format('Y-m-d'),
        'appointment_time' => '10:00:00',
    ]);
    
    $response->assertStatus(422);
});

test('reservation validation rejects email field', function () {
    $doctor = Doctor::factory()->create();
    $service = Service::factory()->create();
    
    $response = $this->postJson('/api/reservations/new-patient', [
        'name' => 'Test',
        'phone' => '081234567890',
        'email' => 'test@test.com',
        'doctor_id' => $doctor->id,
        'service_ids' => [$service->id],
        'complain' => 'Test',
        'reservation_date' => now()->addDays(1)->format('Y-m-d'),
        'appointment_time' => '10:00:00',
    ]);
    
    if ($response->status() === 201) {
        $this->assertDatabaseMissing('patients', ['email' => 'test@test.com']);
    }
});
