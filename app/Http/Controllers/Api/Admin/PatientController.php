<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Http\Requests\UpdatePatientRequest;
use App\Helpers\FileHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PatientController extends Controller
{
    public function index()
    {
        try {
            $patients = Patient::with(['medicalHistory', 'dentalHistory'])
                ->latest()
                ->paginate(10);

            $data = [
                'patients' => $patients->map(function ($patient) {
                    return [
                        'id' => $patient->id,
                        'name' => $patient->name,
                        'phone' => $patient->phone,
                        'birth_date' => $patient->birth_date,
                        'gender' => $patient->gender,
                        'age' => $patient->age,
                        'created_at' => $patient->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
                'pagination' => [
                    'current_page' => $patients->currentPage(),
                    'last_page' => $patients->lastPage(),
                    'per_page' => $patients->perPage(),
                    'total' => $patients->total(),
                ],
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Data pasien berhasil diambil'),
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
            $patient = Patient::with(['medicalHistory', 'dentalHistory', 'reservations.services', 'reservations.doctor', 'rontgens'])
                ->find($id);

            if (!$patient) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Pasien tidak ditemukan'),
                    404
                );
            }

            $data = [
                'id' => $patient->id,
                'name' => $patient->name,
                'phone' => $patient->phone,
                'birth_date' => $patient->birth_date,
                'gender' => $patient->gender,
                'address' => $patient->address,
                'age' => $patient->age,
                'medical_history' => $patient->medicalHistory ? [
                    'id' => $patient->medicalHistory->id,
                    'has_allergy' => $patient->medicalHistory->has_allergy,
                    'allergy_detail' => $patient->medicalHistory->allergy_detail,
                    'has_systemic_disease' => $patient->medicalHistory->has_systemic_disease,
                    'systemic_disease_detail' => $patient->medicalHistory->systemic_disease_detail,
                    'undergoing_treatment' => $patient->medicalHistory->undergoing_treatment,
                    'treatment_detail' => $patient->medicalHistory->treatment_detail,
                    'ever_hospitalized' => $patient->medicalHistory->ever_hospitalized,
                    'hospitalized_reason' => $patient->medicalHistory->hospitalized_reason,
                    'smoking_or_alcohol' => $patient->medicalHistory->smoking_or_alcohol,
                ] : null,
                'dental_history' => $patient->dentalHistory ? [
                    'id' => $patient->dentalHistory->id,
                    'frequent_tooth_pain' => $patient->dentalHistory->frequent_tooth_pain,
                    'tooth_pain_detail' => $patient->dentalHistory->tooth_pain_detail,
                    'bleeding_gums' => $patient->dentalHistory->bleeding_gums,
                    'ever_dental_treatment' => $patient->dentalHistory->ever_dental_treatment,
                    'dental_treatment_detail' => $patient->dentalHistory->dental_treatment_detail,
                    'brushing_frequency' => $patient->dentalHistory->brushing_frequency,
                    'use_floss_or_mouthwash' => $patient->dentalHistory->use_floss_or_mouthwash,
                    'bad_habits' => $patient->dentalHistory->bad_habits,
                    'bad_habits_detail' => $patient->dentalHistory->bad_habits_detail,
                    'ever_braces' => $patient->dentalHistory->ever_braces,
                    'braces_years' => $patient->dentalHistory->braces_years,
                    'root_canal_treatment' => $patient->dentalHistory->root_canal_treatment,
                    'root_canal_detail' => $patient->dentalHistory->root_canal_detail,
                    'dentures' => $patient->dentalHistory->dentures,
                    'routine_checkup' => $patient->dentalHistory->routine_checkup,
                    'dental_checkup_frequency' => $patient->dentalHistory->dental_checkup_frequency,
                    'doctor_notes' => $patient->dentalHistory->doctor_notes,
                ] : null,
                'reservations' => $patient->reservations->map(function ($reservation) {
                    return [
                        'id' => $reservation->id,
                        'complain' => $reservation->complain,
                        'services' => $reservation->services->map(function ($service) {
                            return [
                                'id' => $service->id,
                                'name' => $service->name,
                            ];
                        }),
                        'doctor_name' => $reservation->doctor->name,
                        'reservation_date' => $reservation->reservation_date,
                        'appointment_time' => substr($reservation->appointment_time, 0, 5),
                        'status' => $reservation->status,
                    ];
                }),
                'rontgens' => $patient->rontgens->map(function ($rontgen) {
                    return [
                        'id' => $rontgen->id,
                        'xray_image_url' => $this->getRontgenImageUrl($rontgen->xray_image),
                        'detail' => $rontgen->detail,
                        'created_at' => $rontgen->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
                'created_at' => $patient->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $patient->updated_at->format('Y-m-d H:i:s'),
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Detail pasien berhasil diambil'),
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

            $data = [
                'id' => $patient->id,
                'name' => $patient->name,
                'phone' => $patient->phone,
                'birth_date' => $patient->birth_date,
                'gender' => $patient->gender,
                'address' => $patient->address,
                'age' => $patient->age,
                'medical_history' => $patient->medicalHistory ? [
                    'has_allergy' => $patient->medicalHistory->has_allergy,
                    'allergy_detail' => $patient->medicalHistory->allergy_detail,
                    'has_systemic_disease' => $patient->medicalHistory->has_systemic_disease,
                    'systemic_disease_detail' => $patient->medicalHistory->systemic_disease_detail,
                    'undergoing_treatment' => $patient->medicalHistory->undergoing_treatment,
                    'treatment_detail' => $patient->medicalHistory->treatment_detail,
                    'ever_hospitalized' => $patient->medicalHistory->ever_hospitalized,
                    'hospitalized_reason' => $patient->medicalHistory->hospitalized_reason,
                    'smoking_or_alcohol' => $patient->medicalHistory->smoking_or_alcohol,
                ] : null,
                'dental_history' => $patient->dentalHistory ? [
                    'frequent_tooth_pain' => $patient->dentalHistory->frequent_tooth_pain,
                    'tooth_pain_detail' => $patient->dentalHistory->tooth_pain_detail,
                    'bleeding_gums' => $patient->dentalHistory->bleeding_gums,
                    'ever_dental_treatment' => $patient->dentalHistory->ever_dental_treatment,
                    'dental_treatment_detail' => $patient->dentalHistory->dental_treatment_detail,
                    'brushing_frequency' => $patient->dentalHistory->brushing_frequency,
                    'use_floss_or_mouthwash' => $patient->dentalHistory->use_floss_or_mouthwash,
                    'bad_habits' => $patient->dentalHistory->bad_habits,
                    'bad_habits_detail' => $patient->dentalHistory->bad_habits_detail,
                    'ever_braces' => $patient->dentalHistory->ever_braces,
                    'braces_years' => $patient->dentalHistory->braces_years,
                    'root_canal_treatment' => $patient->dentalHistory->root_canal_treatment,
                    'root_canal_detail' => $patient->dentalHistory->root_canal_detail,
                    'dentures' => $patient->dentalHistory->dentures,
                    'routine_checkup' => $patient->dentalHistory->routine_checkup,
                    'dental_checkup_frequency' => $patient->dentalHistory->dental_checkup_frequency,
                    'doctor_notes' => $patient->dentalHistory->doctor_notes,
                ] : null,
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Data pasien berhasil diupdate'),
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

    private function getRontgenImageUrl(string $fileName): ?string
    {
        if (Storage::disk('public')->exists('rontgen/' . $fileName)) {
            return asset('storage/rontgen/' . $fileName);
        }

        if (Storage::disk('public')->exists('rontgens/' . $fileName)) {
            return asset('storage/rontgens/' . $fileName);
        }

        return null;
    }
}
