<?php

namespace App\Http\Controllers\Api\Public;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Public\TestimonialResource;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index()
    {
        try {
            $testimonials = Testimonial::select('id', 'name', 'rating', 'testimoni', 'photo', 'created_at')
                ->latest()
                ->get();

            return response()->json(
                FileHelper::formatResponse(true, TestimonialResource::collection($testimonials), 'Data testimoni berhasil diambil'),
                200
            );
        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }
}
