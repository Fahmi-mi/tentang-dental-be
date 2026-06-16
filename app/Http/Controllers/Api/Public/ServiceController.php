<?php

namespace App\Http\Controllers\Api\Public;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Public\ServiceDetailResource;
use App\Http\Resources\Public\ServiceListResource;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        try {
            $services = Service::select('id', 'name', 'detail', 'icon')
                ->get();

            return response()->json(
                FileHelper::formatResponse(true, ServiceListResource::collection($services), 'Data layanan berhasil diambil'),
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

            return response()->json(
                FileHelper::formatResponse(true, new ServiceDetailResource($service), 'Detail layanan berhasil diambil'),
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
