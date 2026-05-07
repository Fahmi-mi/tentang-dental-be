<?php

use App\Models\Admin;
use App\Models\Article;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientDentalHistory;
use App\Models\PatientMedicalHistory;
use App\Models\Reservation;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('public article index response contract remains stable', function () {
    $writer = Admin::factory()->create(['role' => 'registration']);

    Article::create([
        'admin_id' => $writer->id,
        'title' => 'Contract Test Article',
        'slug' => 'contract-test-article',
        'content' => 'Content',
        'image' => null,
    ]);

    $response = $this->getJson('/api/articles');

    $response->assertStatus(200);

    expect(array_keys($response->json()))->toEqual(['success', 'data', 'message']);
    expect(array_keys($response->json('data')))->toEqual(['articles', 'pagination']);
    expect(array_keys($response->json('data.articles.0')))->toEqual([
        'id',
        'title',
        'slug',
        'image_url',
        'writer',
        'published_at',
        'published_at_full',
    ]);
    expect(array_keys($response->json('data.pagination')))->toEqual([
        'current_page',
        'last_page',
        'per_page',
        'total',
    ]);
});

test('admin reservation detail response contract remains stable', function () {
    $admin = Admin::factory()->create(['role' => 'registration']);
    Sanctum::actingAs($admin);

    $patient = Patient::factory()->create();
    $doctor = Doctor::factory()->create();
    $service = Service::factory()->create();

    $reservation = Reservation::create([
        'patient_id' => $patient->id,
        'patient_category' => 'existing',
        'doctor_id' => $doctor->id,
        'complain' => 'Contract test complain',
        'reservation_date' => now()->toDateString(),
        'appointment_time' => '10:00:00',
        'status' => 'validated',
    ]);
    $reservation->services()->attach([$service->id]);

    $response = $this->getJson('/api/admin/reservations/' . $reservation->id);

    $response->assertStatus(200);

    expect(array_keys($response->json('data')))->toEqual([
        'id',
        'patient',
        'patient_form',
        'medical_history_form',
        'dental_history_form',
        'services',
        'doctor',
        'complain',
        'reservation_date',
        'appointment_time',
        'birth_date',
        'age',
        'patient_category',
        'status',
        'created_at',
    ]);
    expect(array_keys($response->json('data.patient')))->toEqual([
        'id',
        'name',
        'phone',
        'birth_date',
        'gender',
        'address',
        'medical_history',
        'dental_history',
    ]);
});

test('admin patient detail response contract remains stable', function () {
    $admin = Admin::factory()->create(['role' => 'registration']);
    Sanctum::actingAs($admin);

    $patient = Patient::factory()->create();

    PatientMedicalHistory::create([
        'patient_id' => $patient->id,
        'has_allergy' => false,
        'has_systemic_disease' => false,
        'undergoing_treatment' => false,
        'ever_hospitalized' => false,
        'smoking_or_alcohol' => false,
    ]);

    PatientDentalHistory::create([
        'patient_id' => $patient->id,
        'frequent_tooth_pain' => false,
        'bleeding_gums' => false,
        'ever_dental_treatment' => false,
        'brushing_frequency' => '2',
        'use_floss_or_mouthwash' => false,
        'bad_habits' => false,
        'ever_braces' => false,
        'root_canal_treatment' => false,
        'dentures' => false,
        'routine_checkup' => false,
    ]);

    $response = $this->getJson('/api/admin/patients/' . $patient->id);

    $response->assertStatus(200);

    expect(array_keys($response->json('data')))->toEqual([
        'id',
        'name',
        'phone',
        'birth_date',
        'gender',
        'address',
        'age',
        'medical_history',
        'dental_history',
        'last_reservation',
        'reservations',
        'rontgens',
        'created_at',
        'updated_at',
    ]);
});

test('admin dashboard response contract remains stable', function () {
    $admin = Admin::factory()->create(['role' => 'registration']);
    Sanctum::actingAs($admin);

    $patient = Patient::factory()->create();
    $doctor = Doctor::factory()->create();
    $service = Service::factory()->create(['name' => 'Scaling Contract']);

    $reservation = Reservation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'pending',
        'reservation_date' => now()->toDateString(),
    ]);
    $reservation->services()->attach($service->id);

    $response = $this->getJson('/api/admin/dashboard');

    $response->assertStatus(200);

    expect(array_keys($response->json('data')))->toEqual([
        'daily_statistics',
        'totals',
        'pending_reservations',
        'validated_reservations',
        'completed_reservations',
        'total_patients',
        'monthly_analytics',
        'recent_reservations',
    ]);

    expect(array_keys($response->json('data.daily_statistics')))->toEqual([
        'pending',
        'validated',
        'completed',
        'total',
    ]);
});
