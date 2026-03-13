<?php

use App\Models\Admin;
use App\Models\Promo;
use App\Models\Service;
use App\Models\Article;
use App\Models\Gallery;
use App\Models\Doctor;
use App\Models\Testimonial;
use App\Models\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = Admin::factory()->create(['role' => 'registration']);
    Sanctum::actingAs($this->admin);
    Storage::fake('public');
});

test('admin can create promo', function () {
    $response = $this->postJson('/api/admin/promos', [
        'name' => 'Promo Scaling',
        'detail' => 'Diskon scaling 50%',
        'original_price' => 150000,
        'promo_price' => 75000,
        'image' => UploadedFile::fake()->image('promo.jpg'),
    ]);
    
    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'Promo Scaling',
                'detail' => 'Diskon scaling 50%',
                'original_price' => 150000,
                'promo_price' => 75000,
                'image_url' => asset('storage/promos/' . Promo::first()->image),
            ],
            'message' => 'Promo berhasil ditambahkan'
        ]);
});

test('admin can update promo', function () {
    $promo = Promo::factory()->create(['name' => 'Old Name']);
    
    $response = $this->putJson("/api/admin/promos/{$promo->id}", [
        'name' => 'New Name',
        'detail' => 'Updated detail',
        'original_price' => 200000,
        'promo_price' => 100000,
    ]);
    
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => ['name' => 'New Name']
        ]);
});

test('admin can delete promo', function () {
    $promo = Promo::factory()->create();
    
    $response = $this->deleteJson("/api/admin/promos/{$promo->id}");
    
    $response->assertStatus(200);
    $this->assertDatabaseMissing('promos', ['id' => $promo->id]);
});

test('admin can create service with icon and support_image', function () {
    $response = $this->postJson('/api/admin/services', [
        'name' => 'Scaling',
        'detail' => 'Pembersihan karang gigi',
        'article_content' => 'Konten lengkap...',
        'icon' => UploadedFile::fake()->image('icon.png'),
        'support_image' => UploadedFile::fake()->image('support.jpg'),
    ]);
    
    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'Scaling',
                'detail' => 'Pembersihan karang gigi',
                'article_content' => 'Konten lengkap...',
                'icon_url' => asset('storage/services/' . Service::first()->icon),
                'support_image_url' => asset('storage/services/' . Service::first()->support_image)
            ],
            'message' => 'Layanan berhasil ditambahkan'
        ]);
    
    $this->assertDatabaseMissing('services', ['price' => 0]);
});

test('service validation rejects price and image fields', function () {
    $service = Service::factory()->create();
    
    expect($service->getFillable())
        ->toContain('icon')
        ->toContain('support_image')
        ->not->toContain('price')
        ->not->toContain('image');
});

test('admin can create article with admin_id relationship', function () {
    $response = $this->postJson('/api/admin/articles', [
        'title' => 'Tips Merawat Gigi',
        'content' => 'Konten artikel lengkap...',
        'image' => UploadedFile::fake()->image('article.jpg'),
    ]);
    
    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => [
                'admin_id' => $this->admin->id,
                'title' => 'Tips Merawat Gigi',
                'content' => 'Konten artikel lengkap...',
                'image_url' => asset('storage/articles/' . Article::first()->image),
            ],
            'message' => 'Artikel berhasil dibuat'
        ]);
    
    $this->assertDatabaseHas('articles', [
        'admin_id' => $this->admin->id,
        'title' => 'Tips Merawat Gigi',
        'content' => 'Konten artikel lengkap...',
        'image' => Article::first()->image,
    ]);
});

test('article automatically generates slug from title', function () {
    $response = $this->postJson('/api/admin/articles', [
        'title' => 'Tips Merawat Gigi Anak',
        'content' => 'Konten...',
    ]);
    
    $response->assertStatus(201);
    
    $this->assertDatabaseHas('articles', [
        'title' => 'Tips Merawat Gigi Anak',
        'slug' => 'tips-merawat-gigi-anak',
    ]);
});

test('gallery has only created_at timestamp as per BACKEND_PLAN', function () {
    $gallery = new Gallery();
    
    expect($gallery->timestamps)->toBeFalse();
});

test('admin can create gallery', function () {
    $response = $this->postJson('/api/admin/galleries', [
        'caption' => 'Ruang tunggu klinik',
        'image' => UploadedFile::fake()->image('gallery.jpg'),
    ]);
    
    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Galeri berhasil dibuat'
        ]);
});

test('admin can create doctor with schedule', function () {
    $schedule = [
        'senin' => ['08:00-14:00', '14:00-21:00'],
        'selasa' => [],
        'rabu' => ['14:00-17:00', '17:00-21:00'],
        'kamis' => ['08:00-14:00', '14:00-21:00'],
        'jumat' => ['14:00-17:00', '17:00-21:00'],
        'sabtu' => ['08:00-14:00', '14:00-21:00'],
        'minggu' => ['08:00-14:00', '14:00-21:00'],
    ];
    
    $response = $this->postJson('/api/admin/doctors', [
        'name' => 'Dr. John Smith',
        'specialization' => 'Orthodontist',
        'schedule' => $schedule,
        'statement' => 'Saya siap melayani...',
        'photo' => UploadedFile::fake()->image('doctor.jpg'),
    ]);
    
    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Dokter berhasil dibuat'
        ]);
});

test('admin can create testimonial', function () {
    $response = $this->postJson('/api/admin/testimonials', [
        'name' => 'John Doe',
        'rating' => 5,
        'testimoni' => 'Pelayanan sangat baik!',
        'photo' => UploadedFile::fake()->image('person.jpg'),
    ]);
    
    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Testimoni berhasil dibuat'
        ]);
});

test('testimonial rating must be between 1 and 5', function () {
    $response = $this->postJson('/api/admin/testimonials', [
        'name' => 'John Doe',
        'rating' => 6,
        'testimoni' => 'Test',
    ]);
    
    $response->assertStatus(422);
});

test('admin can create faq', function () {
    $response = $this->postJson('/api/admin/faqs', [
        'question' => 'Berapa biaya scaling?',
        'answer' => 'Biaya scaling mulai dari Rp 150.000',
    ]);
    
    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'FAQ berhasil dibuat'
        ]);
});

test('admin can update faq', function () {
    $faq = Faq::factory()->create([
        'question' => 'Old question?',
        'answer' => 'Old answer',
    ]);
    
    $response = $this->putJson("/api/admin/faqs/{$faq->id}", [
        'question' => 'New question?',
        'answer' => 'New answer',
    ]);
    
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'question' => 'New question?',
                'answer' => 'New answer',
            ]
        ]);
});
