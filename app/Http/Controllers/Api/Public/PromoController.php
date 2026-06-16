<?php

namespace App\Http\Controllers\Api\Public;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Public\PromoResource;
use App\Models\Promo;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function index()
    {
        try {
            $promos = Promo::select('id', 'name', 'image', 'detail', 'original_price', 'promo_price')
                ->latest()
                ->get();

            return response()->json(
                FileHelper::formatResponse(true, PromoResource::collection($promos), 'Data promo berhasil diambil'),
                200
            );
        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function show($id)
    {
        try {
            $promo = Promo::find($id);

            if (!$promo) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Promo tidak ditemukan'),
                    404
                );
            }

            return response()->json(
                FileHelper::formatResponse(true, new PromoResource($promo), 'Detail promo berhasil diambil'),
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
