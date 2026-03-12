<?php

namespace App\Http\Controllers\Api\Public;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function index()
    {
        try {
            $promos = Promo::select('id', 'name', 'image', 'detail', 'original_price', 'promo_price')
                ->latest()
                ->get()
                ->map(function ($promo) {
                    return [
                        'id' => $promo->id,
                        'name' => $promo->name,
                        'image_url' => $promo->image ? asset('storage/promos/' . $promo->image) : null,
                        'detail' => $promo->detail,
                        'original_price' => (float) $promo->original_price,
                        'promo_price' => (float) $promo->promo_price,
                        'discount_percentage' => $promo->original_price > 0 
                            ? round((($promo->original_price - $promo->promo_price) / $promo->original_price) * 100, 0)
                            : 0,
                    ];
                });

            return response()->json(
                FileHelper::formatResponse(true, $promos, 'Data promo berhasil diambil'),
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

            $data = [
                'id' => $promo->id,
                'name' => $promo->name,
                'image_url' => $promo->image ? asset('storage/promos/' . $promo->image) : null,
                'detail' => $promo->detail,
                'original_price' => (float) $promo->original_price,
                'promo_price' => (float) $promo->promo_price,
                'discount_percentage' => $promo->original_price > 0 
                    ? round((($promo->original_price - $promo->promo_price) / $promo->original_price) * 100, 0)
                    : 0,
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Detail promo berhasil diambil'),
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
