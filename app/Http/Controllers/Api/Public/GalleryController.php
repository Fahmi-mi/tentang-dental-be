<?php

namespace App\Http\Controllers\Api\Public;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Public\GalleryResource;
use App\Models\Gallery;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index()
    {
        try {
            $galleries = Gallery::select('id', 'image', 'caption', 'created_at')
                ->latest()
                ->get();

            return response()->json(
                FileHelper::formatResponse(true, GalleryResource::collection($galleries), 'Data galeri berhasil diambil'),
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
