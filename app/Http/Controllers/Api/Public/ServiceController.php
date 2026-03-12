<?php

namespace App\Http\Controllers\Api\Public;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        try {
            $services = Service::select('id', 'name', 'detail', 'icon')
                ->get()
                ->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'detail' => $service->detail,
                        'icon_url' => $service->icon ? asset('storage/services/' . $service->icon) : null,
                    ];
                });

            return response()->json(
                FileHelper::formatResponse(true, $services, 'Data layanan berhasil diambil'),
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
            $service = Service::find($id);

            if (!$service) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Layanan tidak ditemukan'),
                    404
                );
            }

            $data = [
                'id' => $service->id,
                'name' => $service->name,
                'detail' => $service->detail,
                'icon_url' => $service->icon ? asset('storage/services/' . $service->icon) : null,
                'article_content' => $service->article_content,
                'support_image_url' => $service->support_image ? asset('storage/services/' . $service->support_image) : null,
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Detail layanan berhasil diambil'),
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
