<?php

use App\Models\Service;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('service has correct fillable attributes', function () {
    $fillable = [
        'name', 'detail', 'icon', 'article_content', 'support_image'
    ];
    
    $service = new Service();
    expect($service->getFillable())->toEqual($fillable);
    
    expect($service->getFillable())
        ->not->toContain('price')
        ->and($service->getFillable())->not->toContain('image');
});

test('service can be created with valid data', function () {
    $service = Service::create([
        'name' => 'Scaling',
        'detail' => 'Pembersihan karang gigi',
        'icon' => 'scaling_icon.png',
        'article_content' => 'Konten lengkap tentang scaling...',
        'support_image' => 'scaling_support.jpg',
    ]);
    
    expect($service)->toBeInstanceOf(Service::class)
        ->and($service->name)->toBe('Scaling')
        ->and($service->icon)->toBe('scaling_icon.png')
        ->and($service->support_image)->toBe('scaling_support.jpg');
});

test('service belongs to many reservations through pivot table', function () {
    $service = Service::factory()->create();
    $patient = \App\Models\Patient::factory()->create();
    $doctor = \App\Models\Doctor::factory()->create();
    
    $reservation1 = Reservation::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'complain' => 'Test 1',
        'reservation_date' => now()->addDays(1),
        'appointment_time' => '10:00:00',
        'status' => 'pending',
    ]);
    
    $reservation2 = Reservation::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'complain' => 'Test 2',
        'reservation_date' => now()->addDays(2),
        'appointment_time' => '14:00:00',
        'status' => 'pending',
    ]);
    
    $service->reservations()->attach([$reservation1->id, $reservation2->id]);
    
    expect($service->reservations)->toHaveCount(2)
        ->and($service->reservations->first())->toBeInstanceOf(Reservation::class);
});

test('service does not have image field in fillable', function () {
    $service = new Service();
    
    expect($service->getFillable())->not->toContain('image');
});

test('service has icon and support_image fields', function () {
    $service = new Service();
    
    expect($service->getFillable())
        ->toContain('icon')
        ->and($service->getFillable())->toContain('support_image');
});
