<?php

namespace App\Http\Controllers\Api\Public;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index()
    {
        try {
            $testimonials = Testimonial::select('id', 'name', 'rating', 'testimoni', 'photo', 'created_at')
                ->latest()
                ->get()
                ->map(function ($testimonial) {
                    return [
                        'id' => $testimonial->id,
                        'name' => $testimonial->name,
                        'rating' => $testimonial->rating,
                        'testimoni' => $testimonial->testimoni,
                        'photo_url' => $testimonial->photo ? asset('storage/testimonials/' . $testimonial->photo) : null,
                        'created_at' => $testimonial->created_at->format('d M Y'),
                    ];
                });

            return response()->json(
                FileHelper::formatResponse(true, $testimonials, 'Data testimoni berhasil diambil'),
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
