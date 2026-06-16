<?php

namespace App\Http\Controllers\Api\Public;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Public\FaqResource;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        try {
            $faqs = Faq::select('id', 'question', 'answer')
                ->latest()
                ->get();

            return response()->json(
                FileHelper::formatResponse(true, FaqResource::collection($faqs), 'Data FAQ berhasil diambil'),
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
