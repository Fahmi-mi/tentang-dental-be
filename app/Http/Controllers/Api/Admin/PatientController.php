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
            $patients = Patient::with([
                'medicalHistory',
                'dentalHistory',
                'latestReservation.services',
            ])
                ->latest()
                ->paginate(10);
            return $this->paginatedResourceResponse(
                $patients,
                ['patients' => PatientListResource::collection($patients->getCollection())],
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
            $patient = Patient::with([
                'medicalHistory',
                'dentalHistory',
                'reservations.services',
                'reservations.doctor',
                'latestReservation.services',
                'latestReservation.doctor',
                'rontgens.primaryImage',
            ])
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

    public function rontgens($id)
    {
        try {
            $patient = Patient::with([
                'rontgens.doctor',
                'rontgens.examinationImages',
            ])->find($id);

            if (!$patient) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Pasien tidak ditemukan'),
                    404
                );
            }

            $data = [
                'patient' => [
                    'id' => $patient->id,
                    'patient_number' => 'PT-' . str_pad((string) $patient->id, 6, '0', STR_PAD_LEFT),
                    'name' => $patient->name,
                    'phone' => $patient->phone,
                    'birth_date' => $patient->birth_date,
                    'gender' => $patient->gender,
                    'age' => $patient->age,
                ],
                'rontgens' => $patient->rontgens
                    ->sortByDesc('created_at')
                    ->values()
                    ->map(function ($rontgen) {
                        return [
                            'id' => $rontgen->id,
                            'doctor' => [
                                'id' => optional($rontgen->doctor)->id,
                                'name' => optional($rontgen->doctor)->name,
                            ],
                            'detail' => $rontgen->detail,
                            'status' => $rontgen->status,
                            'latest_image_url' => $rontgen->latest_image_url,
                            'images' => $rontgen->examinationImages
                                ->sortByDesc('id')
                                ->values()
                                ->map(function ($image) {
                                    return [
                                        'id' => $image->id,
                                        'image_url' => $this->toPublicImageUrl($image->image_path),
                                        'image_type' => $image->image_type,
                                        'created_at' => optional($image->created_at)->format('Y-m-d H:i:s'),
                                    ];
                                }),
                            'created_at' => optional($rontgen->created_at)->format('Y-m-d H:i:s'),
                        ];
                    }),
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Data rontgen pasien berhasil diambil'),
                200
            );
        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    private function toPublicImageUrl(?string $fileName): ?string
    {
        if (!$fileName) {
            return null;
        }

        if (Storage::disk('public')->exists('rontgen/' . $fileName)) {
            return asset('storage/rontgen/' . $fileName);
        }

        if (Storage::disk('public')->exists('rontgens/' . $fileName)) {
            return asset('storage/rontgens/' . $fileName);
        }

        return null;
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
            if ($request->has('nickname')) $patient->nickname = $request->nickname;
            if ($request->has('phone')) $patient->phone = $request->phone;
            if ($request->has('birth_date')) $patient->birth_date = $request->birth_date;
            if ($request->has('birth_place')) $patient->birth_place = $request->birth_place;
            if ($request->has('gender')) $patient->gender = $request->gender;
            if ($request->has('address')) $patient->address = $request->address;
            if ($request->has('village')) $patient->village = $request->village;
            if ($request->has('district')) $patient->district = $request->district;
            if ($request->has('city')) $patient->city = $request->city;
            if ($request->has('age')) $patient->age = $request->age;
            if ($request->has('occupation')) $patient->occupation = $request->occupation;
            if ($request->has('parent_name')) $patient->parent_name = $request->parent_name;
            if ($request->has('height')) $patient->height = $request->height;
            if ($request->has('weight')) $patient->weight = $request->weight;

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
            $patient = Patient::with(['reservations', 'rontgens.examinationImages'])->find($id);

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
                foreach ($rontgen->examinationImages as $image) {
                    FileHelper::deleteImage('rontgen/' . $image->image_path);
                    FileHelper::deleteImage('rontgens/' . $image->image_path);
                }
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
