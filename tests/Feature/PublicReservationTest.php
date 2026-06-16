<?php

use App\Models\Admin;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('public reservation legacy endpoints are no longer available', function () {
    $response1 = $this->postJson('/api/reservations/check-patient', ['phone' => '081234567890']);
    $response2 = $this->postJson('/api/reservations/new-patient', []);
    $response3 = $this->postJson('/api/reservations/existing-patient', []);

    $response1->assertStatus(404);
    $response2->assertStatus(404);
    $response3->assertStatus(404);
});

test('admin reservation endpoint requires authentication', function () {
    $response = $this->postJson('/api/admin/reservations', []);

    $response->assertStatus(401);
});

test('registration admin can create new patient reservation from unified endpoint', function () {
    $admin = Admin::factory()->create(['role' => 'registration']);
    Sanctum::actingAs($admin);

    $doctor = Doctor::factory()->create();
    $services = Service::factory()->count(2)->create();
    $nextMonday = now()->next('Monday')->format('Y-m-d');

    $response = $this->postJson('/api/admin/reservations', [
        'patient_category' => 'new',
        'name' => 'Jane Doe',
        'phone' => '081234567890',
        'birth_date' => '1996-05-15',
        'age' => 29,
        'doctor_id' => $doctor->id,
        'service_ids' => $services->pluck('id')->toArray(),
        'complain' => 'Mau scaling dan bleaching',
        'reservation_date' => $nextMonday,
        'appointment_time' => '10:00',
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

    $this->assertDatabaseHas('reservations', [
        'complain' => 'Mau scaling dan bleaching',
        'status' => 'validated',
        'patient_category' => 'new',
    ]);
});

test('registration admin can create reservation for existing patient', function () {
    $admin = Admin::factory()->create(['role' => 'registration']);
    Sanctum::actingAs($admin);

    $patient = Patient::factory()->create(['phone' => '081234567890']);
    $doctor = Doctor::factory()->create();
    $service = Service::factory()->create();
    $nextMonday = now()->next('Monday')->format('Y-m-d');

    $response = $this->postJson('/api/admin/reservations', [
        'patient_category' => 'existing',
        'name' => $patient->name,
        'phone' => $patient->phone,
        'doctor_id' => $doctor->id,
        'service_ids' => [$service->id],
        'complain' => 'Checkup rutin',
        'reservation_date' => $nextMonday,
        'appointment_time' => '14:00',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Reservasi berhasil dibuat'
        ]);
});

test('cannot create reservation with more than 3 services', function () {
    $admin = Admin::factory()->create(['role' => 'registration']);
    Sanctum::actingAs($admin);

    $doctor = Doctor::factory()->create();
    $services = Service::factory()->count(4)->create();
    $nextMonday = now()->next('Monday')->format('Y-m-d');

    $response = $this->postJson('/api/admin/reservations', [
        'patient_category' => 'new',
        'name' => 'Test Patient',
        'phone' => '081234567890',
        'doctor_id' => $doctor->id,
        'service_ids' => $services->pluck('id')->toArray(),
        'complain' => 'Test',
        'reservation_date' => $nextMonday,
        'appointment_time' => '10:00',
    ]);

    $response->assertStatus(422);
});

test('rontgen role cannot create reservation', function () {
    $admin = Admin::factory()->create(['role' => 'rontgen']);
    Sanctum::actingAs($admin);

    $doctor = Doctor::factory()->create();
    $service = Service::factory()->create();
    $nextMonday = now()->next('Monday')->format('Y-m-d');

    $response = $this->postJson('/api/admin/reservations', [
        'patient_category' => 'new',
        'name' => 'Role Test',
        'phone' => '081234567891',
        'doctor_id' => $doctor->id,
        'service_ids' => [$service->id],
        'complain' => 'Test role',
        'reservation_date' => $nextMonday,
        'appointment_time' => '10:00',
    ]);

    $response->assertStatus(403);
});
