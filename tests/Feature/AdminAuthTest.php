<?php

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
                'admin' => ['id', 'name', 'email', 'role', 'profile_image_url', 'created_at'],
                'token',
            ],
            'message'
        ]);
});

test('admin can register with name email and password', function () {
    $response = $this->postJson('/api/admin/register', [
        'name' => 'New Admin',
        'email' => 'new-admin@test.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Registrasi admin berhasil',
            'data' => [
                'name' => 'New Admin',
                'email' => 'new-admin@test.com',
                'role' => 'registration',
            ],
        ]);

    $response->assertJsonPath('data.profile_image_url', fn ($value) => is_string($value) && str_contains($value, 'images/default-profile.svg'));

    expect(Admin::where('email', 'new-admin@test.com')->exists())->toBeTrue();
});

test('register ignores profile image and keeps default avatar', function () {
    Storage::fake('public');

    $response = $this->post('/api/admin/register', [
        'name' => 'Admin Image',
        'email' => 'admin-image@test.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'profile_image' => UploadedFile::fake()->image('profile.jpg'),
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('success', true);

    $admin = Admin::where('email', 'admin-image@test.com')->first();

    expect($admin)->not()->toBeNull();
    expect($admin->profile_image)->toBeNull();
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

    $response->assertJsonPath('data.profile_image_url', fn ($value) => is_string($value) && str_contains($value, 'images/default-profile.svg'));
});

test('authenticated admin can update profile image from profile endpoint', function () {
    Storage::fake('public');

    $admin = Admin::factory()->create([
        'role' => 'registration',
        'profile_image' => null,
    ]);

    Sanctum::actingAs($admin);

    $response = $this->put('/api/admin/profile', [
        'name' => 'Updated Name',
        'profile_image' => UploadedFile::fake()->image('profile.jpg'),
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', 'Updated Name');

    $admin = $admin->fresh();

    expect($admin->profile_image)->not()->toBeNull();
    Storage::disk('public')->assertExists('admins/' . $admin->profile_image);
});

test('authenticated admin can change email', function () {
    $admin = Admin::factory()->create([
        'email' => 'admin-old@test.com',
        'password' => bcrypt('password123'),
    ]);

    Sanctum::actingAs($admin);

    $response = $this->putJson('/api/admin/change-email', [
        'current_password' => 'password123',
        'new_email' => 'admin-new@test.com',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Email berhasil diubah',
        ]);

    $response->assertJsonPath('data.email', 'admin-new@test.com');

    expect($admin->fresh()->email)->toBe('admin-new@test.com');
});

test('authenticated admin can change password', function () {
    $admin = Admin::factory()->create([
        'password' => bcrypt('password123'),
    ]);

    Sanctum::actingAs($admin);

    $response = $this->putJson('/api/admin/change-password', [
        'current_password' => 'password123',
        'new_password' => 'newpassword123',
        'new_password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Password berhasil diubah',
        ]);

    expect(password_verify('newpassword123', $admin->fresh()->password))->toBeTrue();
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
