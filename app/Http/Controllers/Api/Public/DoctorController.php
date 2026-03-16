<?php

namespace App\Http\Controllers\Api\Public;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Public\DoctorResource;
use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function index()
    {
        try {
            $doctors = Doctor::select('id', 'name', 'specialization', 'photo', 'schedule', 'statement')
                ->get();

            return response()->json(
                FileHelper::formatResponse(true, DoctorResource::collection($doctors), 'Data dokter berhasil diambil')
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
            $doctor = Doctor::find($id);

            if (!$doctor) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Dokter tidak ditemukan'),
                    404
                );
            }

            return response()->json(
                FileHelper::formatResponse(true, new DoctorResource($doctor), 'Detail dokter berhasil diambil'),
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
