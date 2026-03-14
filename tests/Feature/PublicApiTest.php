<?php

use App\Models\Promo;
use App\Models\Service;
use App\Models\Article;
use App\Models\Gallery;
use App\Models\Doctor;
use App\Models\Testimonial;
use App\Models\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can get list of promos', function () {
    $promo = Promo::factory()->count(3)->create();
    
    $response = $this->getJson('/api/promos');
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'name',
                    'original_price',
                    'discount_percentage'
                ]
            ],
            'message'
        ]);
});

test('can get single promo by id', function () {
    $promo = Promo::factory()->create([
        'name' => 'Promo Scaling',
        'original_price' => 150000,
        'promo_price' => 99000,
    ]);
    
    $response = $this->getJson("/api/promos/{$promo->id}");
    
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $promo->id,
                'name' => 'Promo Scaling',
                'detail' => $promo->detail,
                'original_price' => 150000,
                'promo_price' => 99000,
                'discount_percentage' => 34
            ],
            'message' => 'Detail promo berhasil diambil'
        ]);
});

test('can get list of services', function () {
    Service::factory()->count(5)->create();
    
    $response = $this->getJson('/api/services');
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'name', 'detail', 'icon_url']
            ],
            'message'
        ]);
});

test('can get single service by id with article content', function () {
    $service = Service::factory()->create([
        'name' => 'Scaling',
        'detail' => 'Pembersihan karang gigi',
        'article_content' => 'Konten lengkap tentang scaling...',
    ]);
    
    $response = $this->getJson("/api/services/{$service->id}");
    
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $service->id,
                'name' => 'Scaling',
                'article_content' => 'Konten lengkap tentang scaling...',
            ]
        ]);
});

test('service response has icon and support_image not image field', function () {
    $service = Service::factory()->create([
        'icon' => 'icon.png',
        'support_image' => 'support.jpg',
    ]);
    
    $response = $this->getJson("/api/services/{$service->id}");
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['icon_url', 'support_image_url']
        ]);
    
    $response->assertJsonMissing(['price', 'image']);
});

test('can get list of articles with writer info', function () {
    $admin = \App\Models\Admin::factory()->create(['name' => 'Admin Writer']);
    Article::factory()->count(3)->create(['admin_id' => $admin->id]);
    
    $response = $this->getJson('/api/articles');
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'articles' => [
                    '*' => ['id', 'title', 'slug', 'image_url', 'writer', 'published_at']
                ],
                'pagination'
            ],
            'message'
        ]);
});

test('can get single article by slug', function () {
    $admin = \App\Models\Admin::factory()->create(['name' => 'John Admin']);
    $article = Article::factory()->create([
        'admin_id' => $admin->id,
        'title' => 'Tips Merawat Gigi',
        'slug' => 'tips-merawat-gigi',
        'content' => 'Konten artikel lengkap...',
    ]);
    
    $response = $this->getJson("/api/articles/{$article->slug}");
    
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'title' => 'Tips Merawat Gigi',
                'slug' => 'tips-merawat-gigi',
                'writer' => 'John Admin',
            ]
        ]);
});

test('returns 404 for non-existent article slug', function () {
    $response = $this->getJson('/api/articles/non-existent-slug');
    
    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Artikel tidak ditemukan'
        ]);
});

test('can get list of galleries', function () {
    Gallery::factory()->count(5)->create();
    
    $response = $this->getJson('/api/galleries');
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id', 'image_url', 'caption', 'uploaded_at'
                ]
            ],
            'message'
        ]);
});

test('can get list of doctors with schedule', function () {
    Doctor::factory()->count(2)->create();
    
    $response = $this->getJson('/api/doctors');
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'name', 'specialization', 'photo_url', 'schedule', 'statement']
            ],
            'message'
        ]);
});

test('can get single doctor by id', function () {
    $doctor = Doctor::factory()->create([
        'name' => 'Dr. John Dentist',
        'specialization' => 'Orthodontist',
    ]);
    
    $response = $this->getJson("/api/doctors/{$doctor->id}");
    
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $doctor->id,
                'name' => 'Dr. John Dentist',
                'specialization' => 'Orthodontist',
            ]
        ]);
});

test('returns 404 for non-existent doctor', function () {
    $response = $this->getJson('/api/doctors/99999');
    
    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Dokter tidak ditemukan'
        ]);
});

test('can get list of testimonials', function () {
    Testimonial::factory()->count(5)->create();
    
    $response = $this->getJson('/api/testimonials');
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id', 'name', 'rating', 'testimoni', 'photo_url'
                ]
            ],
            'message'
        ]);
});

test('can get list of faqs', function () {
    Faq::factory()->count(5)->create();
    
    $response = $this->getJson('/api/faqs');
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id', 'question', 'answer'
                ]
            ],
            'message'
        ]);
});
