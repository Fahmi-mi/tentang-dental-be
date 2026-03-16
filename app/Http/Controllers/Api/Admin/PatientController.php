<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\FormatsApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\PatientDetailResource;
use App\Http\Resources\Admin\PatientListResource;
use App\Http\Resources\Admin\PatientUpdateResource;
use App\Models\Patient;
use App\Http\Requests\UpdatePatientRequest;
use App\Helpers\FileHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PatientController extends Controller
{
    use FormatsApiResponse;

    public function index()
    {
        try {
            $patients = Patient::with(['medicalHistory', 'dentalHistory'])
                ->latest()
                ->paginate(10);
            return $this->paginatedResourceResponse(
                $patients,
                'patients',
                PatientListResource::collection($patients->getCollection())->resolve(),
                'Data pasien berhasil diambil'
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
            $patient = Patient::with(['medicalHistory', 'dentalHistory', 'reservations.services', 'reservations.doctor', 'rontgens'])
                ->find($id);

            if (!$patient) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Pasien tidak ditemukan'),
                    404
                );
            }

            return response()->json(
                FileHelper::formatResponse(true, new PatientDetailResource($patient), 'Detail pasien berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function update(UpdatePatientRequest $request, $id)
    {
        try {
            $patient = Patient::with(['medicalHistory', 'dentalHistory'])->find($id);

            if (!$patient) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Pasien tidak ditemukan'),
                    404
                );
            }

            if ($request->has('name')) $patient->name = $request->name;
            if ($request->has('phone')) $patient->phone = $request->phone;
            if ($request->has('birth_date')) $patient->birth_date = $request->birth_date;
            if ($request->has('gender')) $patient->gender = $request->gender;
            if ($request->has('address')) $patient->address = $request->address;
            if ($request->has('age')) $patient->age = $request->age;

            $patient->save();

            if ($request->has('medical_history')) {
                $medicalData = $request->medical_history;
                
                if ($patient->medicalHistory) {
                    $patient->medicalHistory->update($medicalData);
                } else {
                    $patient->medicalHistory()->create($medicalData);
                }
            }

            if ($request->has('dental_history')) {
                $dentalData = $request->dental_history;
                
                if ($patient->dentalHistory) {
                    $patient->dentalHistory->update($dentalData);
                } else {
                    $patient->dentalHistory()->create($dentalData);
                }
            }

            $patient->load(['medicalHistory', 'dentalHistory']);

            return response()->json(
                FileHelper::formatResponse(true, new PatientUpdateResource($patient), 'Data pasien berhasil diupdate'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal mengupdate data pasien: ' . $e->getMessage()),
                500
            );
        }
    }

    public function destroy($id)
    {
        try {
            $patient = Patient::with(['reservations', 'rontgens'])->find($id);

            if (!$patient) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Pasien tidak ditemukan'),
                    404
                );
            }

            $activeReservations = $patient->reservations()
                ->whereIn('status', ['pending', 'validated'])
                ->count();

            if ($activeReservations > 0) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Tidak dapat menghapus pasien dengan reservasi aktif'),
                    400
                );
            }

            foreach ($patient->rontgens as $rontgen) {
                FileHelper::deleteImage('rontgen/' . $rontgen->xray_image);
            }

            $patient->delete();

            return response()->json(
                FileHelper::formatResponse(true, null, 'Data pasien berhasil dihapus'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menghapus pasien: ' . $e->getMessage()),
                500
            );
        }
    }

    public function downloadPdf($id)
    {
        try {
            $patient = Patient::with(['medicalHistory', 'dentalHistory'])->find($id);

            if (!$patient) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Pasien tidak ditemukan'),
                    404
                );
            }

            $pdf = Pdf::loadView('pdf.patient-data', [
                'patient' => $patient,
                'medicalHistory' => $patient->medicalHistory,
                'dentalHistory' => $patient->dentalHistory,
            ])->setPaper('a4', 'portrait');

            $fileName = 'patient_' . $patient->id . '_' . now()->format('Ymd_His') . '.pdf';

            return $pdf->download($fileName);
        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal download PDF data pasien: ' . $e->getMessage()),
                500
            );
        }
    }
}
