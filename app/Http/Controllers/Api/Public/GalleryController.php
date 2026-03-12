<?php

namespace App\Http\Controllers\Api\Public;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index()
    {
        try {
            $galleries = Gallery::select('id', 'image', 'caption', 'created_at')
                ->latest()
                ->get()
                ->map(function ($gallery) {
                    return [
                        'id' => $gallery->id,
                        'image_url' => $gallery->image ? asset('storage/galleries/' . $gallery->image) : null,
                        'caption' => $gallery->caption,
                        'uploaded_at' => $gallery->created_at->format('d M Y'),
                    ];
                });

            return response()->json(
                FileHelper::formatResponse(true, $galleries, 'Data galeri berhasil diambil'),
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
