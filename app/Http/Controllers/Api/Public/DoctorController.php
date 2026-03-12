<?php

namespace App\Http\Controllers\Api\Public;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function index()
    {
        try {
            $doctors = Doctor::select('id', 'name', 'specialization', 'photo', 'schedule', 'statement')
                ->get()
                ->map(function ($doctor) {
                    return [
                        'id' => $doctor->id,
                        'name' => $doctor->name,
                        'specialization' => $doctor->specialization,
                        'photo_url' => $doctor->photo ? asset('storage/doctors/' . $doctor->photo) : null,
                        'schedule' => $doctor->schedule,
                        'statement' => $doctor->statement,
                    ];
                });

            return response()->json(
                FileHelper::formatResponse(true, $doctors, 'Data dokter berhasil diambil')
            );
        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage())
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

            $data = [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'specialization' => $doctor->specialization,
                'photo_url' => $doctor->photo ? asset('storage/doctors/' . $doctor->photo) : null,
                'schedule' => $doctor->schedule,
                'statement' => $doctor->statement,
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Detail dokter berhasil diambil'),
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
