<?php

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('admin can login with valid credentials', function () {
    $admin = Admin::factory()->create([
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
        'role' => 'registration',
    ]);
    
    $response = $this->postJson('/api/admin/login', [
        'email' => 'admin@test.com',
        'password' => 'password',
    ]);
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'admin' => ['id', 'name', 'email', 'role'],
                'token',
            ],
            'message'
        ]);
});

test('admin login fails with invalid credentials', function () {
    Admin::factory()->create([
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
    ]);
    
    $response = $this->postJson('/api/admin/login', [
        'email' => 'admin@test.com',
        'password' => 'wrong-password',
    ]);
    
    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Email atau password salah'
        ]);
});

test('authenticated admin can get their profile', function () {
    $admin = Admin::factory()->create([
        'name' => 'Test Admin',
        'email' => 'admin@test.com',
        'role' => 'registration',
    ]);
    
    Sanctum::actingAs($admin);
    
    $response = $this->getJson('/api/admin/me');
    
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'Test Admin',
                'email' => 'admin@test.com',
                'role' => 'registration',
            ]
        ]);
});

test('admin can logout', function () {
    $admin = Admin::factory()->create();
    Sanctum::actingAs($admin);
    
    $response = $this->postJson('/api/admin/logout');
    
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
});

test('unauthenticated request returns 401', function () {
    $response = $this->getJson('/api/admin/me');
    
    $response->assertStatus(401);
});

test('admin with wrong role cannot access restricted endpoint', function () {
    $rontgenAdmin = Admin::factory()->create(['role' => 'rontgen']);
    Sanctum::actingAs($rontgenAdmin);
    
    $response = $this->postJson('/api/admin/promos', [
        'name' => 'Test Promo',
    ]);
    
    $response->assertStatus(403);
});
