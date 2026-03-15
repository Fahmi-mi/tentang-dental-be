<?php

use App\Models\Admin;
use App\Models\Patient;
use App\Models\Rontgen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

test('registration admin can view rontgen list', function () {
    $admin = Admin::factory()->create(['role' => 'registration']);
    Sanctum::actingAs($admin);
    
    $patient = Patient::factory()->create();
    Rontgen::factory()->count(3)->create(['patient_id' => $patient->id]);
    
    $response = $this->getJson('/api/admin/rontgens');
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'rontgens' => [
                    '*' => ['id', 'patient', 'xray_image_url', 'detail', 'created_at']
                ],
                'pagination'
            ]
        ]);
});

test('rontgen admin can create rontgen', function () {
    $admin = Admin::factory()->create(['role' => 'rontgen']);
    Sanctum::actingAs($admin);
    
    $patient = Patient::factory()->create();
    
    $response = $this->postJson('/api/admin/rontgens', [
        'patient_id' => $patient->id,
        'xray_image' => UploadedFile::fake()->image('rontgen.jpg'),
        'detail' => 'Rontgen panoramic gigi',
    ]);
    
    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Data rontgen berhasil ditambahkan'
        ]);
    
    $this->assertDatabaseHas('rontgen', [
        'patient_id' => $patient->id,
        'detail' => 'Rontgen panoramic gigi',
    ]);
});

test('rontgen admin can update rontgen', function () {
    $admin = Admin::factory()->create(['role' => 'rontgen']);
    Sanctum::actingAs($admin);
    
    $patient = Patient::factory()->create();
    $rontgen = Rontgen::factory()->create([
        'patient_id' => $patient->id,
        'detail' => 'Old detail',
    ]);
    
    $response = $this->putJson("/api/admin/rontgens/{$rontgen->id}", [
        'detail' => 'Updated detail',
    ]);
    
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'detail' => 'Updated detail',
            ]
        ]);
});

test('rontgen admin can delete rontgen', function () {
    $admin = Admin::factory()->create(['role' => 'rontgen']);
    Sanctum::actingAs($admin);
    
    $patient = Patient::factory()->create();
    $rontgen = Rontgen::factory()->create(['patient_id' => $patient->id]);
    
    $response = $this->deleteJson("/api/admin/rontgens/{$rontgen->id}");
    
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Data rontgen berhasil dihapus'
        ]);
    
    $this->assertDatabaseMissing('rontgen', ['id' => $rontgen->id]);
});

test('patient can have multiple rontgen images', function () {
    $patient = Patient::factory()->create();
    
    Rontgen::factory()->count(5)->create(['patient_id' => $patient->id]);
    
    expect($patient->rontgens)->toHaveCount(5);
});

test('rontgen belongs to patient', function () {
    $patient = Patient::factory()->create(['name' => 'John Doe']);
    $rontgen = Rontgen::factory()->create(['patient_id' => $patient->id]);
    
    expect($rontgen->patient)->toBeInstanceOf(Patient::class)
        ->and($rontgen->patient->name)->toBe('John Doe');
});

test('registration admin cannot create rontgen', function () {
    $admin = Admin::factory()->create(['role' => 'registration']);
    Sanctum::actingAs($admin);
    
    $patient = Patient::factory()->create();
    
    $response = $this->postJson('/api/admin/rontgens', [
        'patient_id' => $patient->id,
        'detail' => 'Test',
    ]);
    
    $response->assertStatus(403);
});

test('rontgen admin can view patient data', function () {
    $admin = Admin::factory()->create(['role' => 'rontgen']);
    Sanctum::actingAs($admin);
    
    $patient = Patient::factory()->create();
    
    $response = $this->getJson("/api/admin/patients/{$patient->id}");
    
    $response->assertStatus(200);
});

test('rontgen admin cannot update patient data', function () {
    $admin = Admin::factory()->create(['role' => 'rontgen']);
    Sanctum::actingAs($admin);
    
    $patient = Patient::factory()->create();
    
    $response = $this->putJson("/api/admin/patients/{$patient->id}", [
        'name' => 'Updated Name',
    ]);
    
    $response->assertStatus(403);
});
