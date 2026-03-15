<?php

use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\PatientController;
use App\Http\Controllers\Api\Admin\RontgenController;
use App\Http\Controllers\Api\Admin\PromoController as AdminPromoController;
use App\Http\Controllers\Api\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Api\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Api\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Api\Admin\DoctorController as AdminDoctorController;
use App\Http\Controllers\Api\Admin\TestimonialController as AdminTestimonialController;
use App\Http\Controllers\Api\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Api\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Api\Public\ArticleController;
use App\Http\Controllers\Api\Public\DoctorController;
use App\Http\Controllers\Api\Public\FaqController;
use App\Http\Controllers\Api\Public\GalleryController;
use App\Http\Controllers\Api\Public\PromoController;
use App\Http\Controllers\Api\Public\ServiceController;
use App\Http\Controllers\Api\Public\TestimonialController;
use Illuminate\Support\Facades\Route;

Route::get('/promos', [PromoController::class, 'index']);
Route::get('/promos/{id}', [PromoController::class, 'show']);

Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{id}', [ServiceController::class, 'show']);

Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{slug}', [ArticleController::class, 'show']);

Route::get('/galleries', [GalleryController::class, 'index']);

Route::get('/doctors', [DoctorController::class, 'index']);
Route::get('/doctors/{id}', [DoctorController::class, 'show']);

Route::get('/testimonials', [TestimonialController::class, 'index']);

Route::get('/faqs', [FaqController::class, 'index']);

Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        // Auth management
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
        
        Route::middleware('role:registration')->group(function () {
            // CMS Management
            Route::apiResource('promos', AdminPromoController::class);
            Route::apiResource('services', AdminServiceController::class);
            Route::apiResource('articles', AdminArticleController::class);
            Route::apiResource('galleries', AdminGalleryController::class);
            Route::apiResource('doctors', AdminDoctorController::class);
            Route::apiResource('testimonials', AdminTestimonialController::class);
            Route::apiResource('faqs', AdminFaqController::class);
            
            // Dashboard & Analytics
            Route::get('/dashboard', [DashboardController::class, 'index']);
            Route::get('/dashboard/reservation-stats', [DashboardController::class, 'reservationStats']);
            Route::get('/dashboard/service-analytics', [DashboardController::class, 'serviceAnalytics']);
            
            // Reservation Management
            Route::post('/reservations', [AdminReservationController::class, 'store']);
            Route::get('/reservations', [AdminReservationController::class, 'index']);
            Route::get('/reservations/{id}', [AdminReservationController::class, 'show']);
            Route::put('/reservations/{id}', [AdminReservationController::class, 'update']);
            Route::put('/reservations/{id}/patient-details', [AdminReservationController::class, 'updatePatientDetails']);
            Route::delete('/reservations/{id}', [AdminReservationController::class, 'destroy']);
            
            // Patient Management (write)
            Route::put('/patients/{id}', [PatientController::class, 'update']);
            Route::delete('/patients/{id}', [PatientController::class, 'destroy']);
        });

        Route::middleware('role:registration,rontgen')->group(function () {
            // Shared read/download access
            Route::get('/patients', [PatientController::class, 'index']);
            Route::get('/patients/{id}', [PatientController::class, 'show']);
            Route::get('/patients/{id}/download-pdf', [PatientController::class, 'downloadPdf']);

            Route::get('/rontgens', [RontgenController::class, 'index']);
            Route::get('/rontgens/{id}', [RontgenController::class, 'show']);
            Route::get('/rontgens/{id}/download', [RontgenController::class, 'download']);
        });
        
        Route::middleware('role:rontgen')->group(function () {
            // Rontgen Management (write)
            Route::post('/rontgens', [RontgenController::class, 'store']);
            Route::put('/rontgens/{id}', [RontgenController::class, 'update']);
            Route::delete('/rontgens/{id}', [RontgenController::class, 'destroy']);
        });
    });
});
