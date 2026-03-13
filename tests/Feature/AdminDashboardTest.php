<?php

use App\Models\Admin;
use App\Models\Reservation;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = Admin::factory()->create(['role' => 'registration']);
    Sanctum::actingAs($this->admin);
});

test('dashboard returns daily statistics', function () {
    $patient = Patient::factory()->create();
    $doctor = Doctor::factory()->create();
    
    Reservation::factory()->count(5)->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'pending',
        'reservation_date' => now(),
    ]);
    
    Reservation::factory()->count(3)->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'validated',
        'reservation_date' => now(),
    ]);
    
    $response = $this->getJson('/api/admin/dashboard');
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'total_reservations_today',
                'pending_reservations',
                'validated_reservations',
                'completed_reservations',
                'cancelled_reservations',
                'total_patients',
            ]
        ]);
});

test('reservation stats returns monthly data', function () {
    $patient = Patient::factory()->create();
    $doctor = Doctor::factory()->create();
    
    $currentMonth = now()->format('Y-m');
    
    Reservation::factory()->count(10)->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'reservation_date' => now(),
    ]);
    
    $response = $this->getJson("/api/admin/dashboard/reservation-stats?month={$currentMonth}");
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'month',
                'total_reservations',
                'by_status' => [
                    'pending',
                    'validated',
                    'completed',
                    'cancelled',
                ],
                'by_date',
            ]
        ]);
});

test('service analytics returns reservation count per service', function () {
    $patient = Patient::factory()->create();
    $doctor = Doctor::factory()->create();
    $service1 = Service::factory()->create(['name' => 'Scaling']);
    $service2 = Service::factory()->create(['name' => 'Bleaching']);
    
    $currentMonth = now()->format('Y-m');
    
    $reservation1 = Reservation::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'complain' => 'Test',
        'reservation_date' => now(),
        'appointment_time' => '10:00:00',
        'status' => 'pending',
    ]);
    $reservation1->services()->attach([$service1->id, $service2->id]);
    
    $reservation2 = Reservation::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'complain' => 'Test',
        'reservation_date' => now(),
        'appointment_time' => '14:00:00',
        'status' => 'validated',
    ]);
    $reservation2->services()->attach([$service1->id]);
    
    $response = $this->getJson("/api/admin/dashboard/service-analytics?month={$currentMonth}");
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'month',
                'services' => [
                    '*' => [
                        'service_id',
                        'service_name',
                        'reservation_count',
                    ]
                ]
            ]
        ]);
    
    $data = $response->json('data.services');
    $scalingService = collect($data)->firstWhere('service_name', 'Scaling');
    
    expect($scalingService['reservation_count'])->toBe(2);
});

test('dashboard statistics show correct counts', function () {
    $patient1 = Patient::factory()->create();
    $patient2 = Patient::factory()->create();
    $doctor = Doctor::factory()->create();
    
    Reservation::factory()->count(3)->create([
        'patient_id' => $patient1->id,
        'doctor_id' => $doctor->id,
        'status' => 'pending',
        'reservation_date' => now(),
    ]);
    
    Reservation::factory()->count(2)->create([
        'patient_id' => $patient2->id,
        'doctor_id' => $doctor->id,
        'status' => 'validated',
        'reservation_date' => now(),
    ]);
    
    $response = $this->getJson('/api/admin/dashboard');
    
    $response->assertStatus(200);
    
    $data = $response->json('data');
    expect($data['pending_reservations'])->toBe(3)
        ->and($data['validated_reservations'])->toBe(2)
        ->and($data['total_patients'])->toBe(2);
});

test('dashboard endpoint requires authentication', function () {
    $response = $this->getJson('/api/admin/dashboard');
    
    $response->assertStatus(401);
});

test('dashboard endpoint requires registration role', function () {
    $rontgenAdmin = Admin::factory()->create(['role' => 'rontgen']);
    Sanctum::actingAs($rontgenAdmin);
    
    $response = $this->getJson('/api/admin/dashboard');
    
    $response->assertStatus(403);
});
