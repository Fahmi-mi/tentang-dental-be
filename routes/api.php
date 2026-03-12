<?php

use App\Http\Controllers\Api\Public\ArticleController;
use App\Http\Controllers\Api\Public\DoctorController;
use App\Http\Controllers\Api\Public\FaqController;
use App\Http\Controllers\Api\Public\GalleryController;
use App\Http\Controllers\Api\Public\PromoController;
use App\Http\Controllers\Api\Public\ReservationController;
use App\Http\Controllers\Api\Public\ServiceController;
use App\Http\Controllers\Api\Public\TestimonialController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('public')->group(function () {
    
    // Doctors
    Route::get('/doctors', [DoctorController::class, 'index']);
    Route::get('/doctors/{id}', [DoctorController::class, 'show']);
    
    // Services
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/{id}', [ServiceController::class, 'show']);
    
    // Promos
    Route::get('/promos', [PromoController::class, 'index']);
    Route::get('/promos/{id}', [PromoController::class, 'show']);
    
    // Articles
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{slug}', [ArticleController::class, 'show']);
    
    // Gallery
    Route::get('/galleries', [GalleryController::class, 'index']);
    
    // Testimonials
    Route::get('/testimonials', [TestimonialController::class, 'index']);
    
    // FAQs
    Route::get('/faqs', [FaqController::class, 'index']);
    
    // Reservations
    Route::post('/reservations/check-patient', [ReservationController::class, 'checkPatient']);
    Route::post('/reservations/new-patient', [ReservationController::class, 'storeNewPatient']);
    Route::post('/reservations/existing-patient', [ReservationController::class, 'storeExistingPatient']);
});
