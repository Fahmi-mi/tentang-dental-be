<?php

use App\Models\Admin;
use App\Models\Patient;
use App\Models\Reservation;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\PatientMedicalHistory;
use App\Models\PatientDentalHistory;
use App\Models\Rontgen;
use App\Models\ExaminationImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = Admin::factory()->create(['role' => 'registration']);
    Sanctum::actingAs($this->admin);
});

test('admin can get list of patients without email field', function () {
    $patient = Patient::factory()->create();
    $doctor = Doctor::factory()->create();
    $service = Service::factory()->create(['name' => 'Scaling']);

    $reservation = Reservation::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'complain' => 'Kontrol',
        'reservation_date' => now(),
        'appointment_time' => '09:00:00',
        'status' => 'validated',
    ]);
    $reservation->services()->attach([$service->id]);

    Patient::factory()->count(2)->create();
    
    $response = $this->getJson('/api/admin/patients');
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'patients' => [
                    '*' => [
                        'id',
                        'patient_number',
                        'name',
                        'phone',
                        'gender',
                        'age',
                        'birth_date',
                        'latest_reservation_date',
                        'latest_services',
                    ]
                ],
                'pagination'
            ]
        ]);
    
    $response->assertJsonPath('data.patients.0.patient_number', 'PT-' . str_pad((string) $patient->id, 6, '0', STR_PAD_LEFT));
    $response->assertJsonPath('data.patients.0.latest_services.0', 'Scaling');
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
                'last_reservation' => [
                    'id', 'doctor_name', 'reservation_date', 'appointment_time', 'status',
                    'services' => [
                        '*' => ['id', 'name']
                    ]
                ],
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

    $response->assertJsonPath('data.last_reservation.services.0.name', 'Scaling');
    $response->assertJsonPath('data.last_reservation.services.1.name', 'Bleaching');
});

test('admin can get patient rontgens endpoint with all images', function () {
    $patient = Patient::factory()->create(['name' => 'Jane Doe']);
    $doctor = Doctor::factory()->create(['name' => 'Dr. Andi']);

    $rontgen = Rontgen::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'detail' => 'Kontrol rahang',
        'status' => 'selesai',
    ]);

    ExaminationImage::create([
        'rontgen_id' => $rontgen->id,
        'image_path' => 'sample-1.jpg',
        'image_type' => 'xray',
    ]);

    ExaminationImage::create([
        'rontgen_id' => $rontgen->id,
        'image_path' => 'sample-2.jpg',
        'image_type' => 'intraoral',
    ]);

    $response = $this->getJson("/api/admin/patients/{$patient->id}/rontgens");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'patient' => ['id', 'patient_number', 'name', 'phone', 'birth_date', 'gender', 'age'],
                'rontgens' => [
                    '*' => [
                        'id',
                        'doctor' => ['id', 'name'],
                        'detail',
                        'status',
                        'latest_image_url',
                        'images' => [
                            '*' => ['id', 'image_url', 'image_type', 'created_at']
                        ],
                        'created_at',
                    ]
                ]
            ]
        ]);

    $response->assertJsonPath('data.patient.name', 'Jane Doe');
    $response->assertJsonCount(2, 'data.rontgens.0.images');
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

test('admin update persists and returns extended patient profile fields', function () {
    $patient = Patient::factory()->create();

    $payload = [
        'name' => 'Updated Extended Name',
        'nickname' => 'Fahmi',
        'phone' => $patient->phone,
        'birth_place' => 'Bandung',
        'birth_date' => '1995-03-10',
        'gender' => 'male',
        'address' => 'Jl. Mawar',
        'village' => 'Cibiru',
        'district' => 'Cileunyi',
        'city' => 'Bandung',
        'age' => 31,
        'occupation' => 'Developer',
        'parent_name' => 'Bapak A',
        'height' => 172.5,
        'weight' => 67.3,
    ];

    $updateResponse = $this->putJson("/api/admin/patients/{$patient->id}", $payload);

    $updateResponse->assertStatus(200)
        ->assertJsonPath('data.nickname', 'Fahmi')
        ->assertJsonPath('data.birth_place', 'Bandung')
        ->assertJsonPath('data.village', 'Cibiru')
        ->assertJsonPath('data.district', 'Cileunyi')
        ->assertJsonPath('data.city', 'Bandung')
        ->assertJsonPath('data.occupation', 'Developer')
        ->assertJsonPath('data.parent_name', 'Bapak A')
        ->assertJsonPath('data.height', '172.50')
        ->assertJsonPath('data.weight', '67.30');

    $this->assertDatabaseHas('patients', [
        'id' => $patient->id,
        'nickname' => 'Fahmi',
        'birth_place' => 'Bandung',
        'village' => 'Cibiru',
        'district' => 'Cileunyi',
        'city' => 'Bandung',
        'occupation' => 'Developer',
        'parent_name' => 'Bapak A',
    ]);

    $detailResponse = $this->getJson("/api/admin/patients/{$patient->id}");

    $detailResponse->assertStatus(200)
        ->assertJsonPath('data.nickname', 'Fahmi')
        ->assertJsonPath('data.birth_place', 'Bandung')
        ->assertJsonPath('data.village', 'Cibiru')
        ->assertJsonPath('data.district', 'Cileunyi')
        ->assertJsonPath('data.city', 'Bandung')
        ->assertJsonPath('data.occupation', 'Developer')
        ->assertJsonPath('data.parent_name', 'Bapak A')
        ->assertJsonPath('data.height', '172.50')
        ->assertJsonPath('data.weight', '67.30');
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
