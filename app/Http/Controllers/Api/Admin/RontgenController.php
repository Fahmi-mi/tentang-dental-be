<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rontgen;
use App\Models\Patient;
use App\Http\Requests\StoreRontgenRequest;
use App\Http\Requests\UpdateRontgenRequest;
use App\Helpers\FileHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class RontgenController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Rontgen::with('patient');

            if ($request->has('patient_id')) {
                $query->where('patient_id', $request->patient_id);
            }

            $rontgens = $query->latest()->paginate(10);

            $data = [
                'rontgens' => $rontgens->map(function ($rontgen) {
                    return [
                        'id' => $rontgen->id,
                        'patient' => [
                            'id' => $rontgen->patient->id,
                            'name' => $rontgen->patient->name,
                            'phone' => $rontgen->patient->phone,
                        ],
                        'xray_image_url' => $this->getRontgenImageUrl($rontgen->xray_image),
                        'detail' => $rontgen->detail,
                        'created_at' => $rontgen->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
                'pagination' => [
                    'current_page' => $rontgens->currentPage(),
                    'last_page' => $rontgens->lastPage(),
                    'per_page' => $rontgens->perPage(),
                    'total' => $rontgens->total(),
                ],
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Data rontgen berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function store(StoreRontgenRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $patient = Patient::find($request->patient_id);
            
            if (!$patient) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Pasien tidak ditemukan'),
                    404
                );
            }

            $imageName = FileHelper::uploadImage($request->file('xray_image'), 'rontgen');

            if (!$imageName) {
                throw new \Exception('Gagal mengupload gambar rontgen');
            }

            $rontgen = Rontgen::create([
                'patient_id' => $request->patient_id,
                'xray_image' => $imageName,
                'detail' => $request->detail ?? null,
            ]);

            DB::commit();

            $data = [
                'id' => $rontgen->id,
                'patient' => [
                    'id' => $patient->id,
                    'name' => $patient->name,
                    'phone' => $patient->phone,
                ],
                'xray_image_url' => $this->getRontgenImageUrl($rontgen->xray_image),
                'detail' => $rontgen->detail,
                'created_at' => $rontgen->created_at->format('Y-m-d H:i:s'),
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Data rontgen berhasil ditambahkan'),
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($imageName)) {
                FileHelper::deleteImage('rontgen/' . $imageName);
            }

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menambahkan rontgen: ' . $e->getMessage()),
                500
            );
        }
    }

    public function show($id)
    {
        try {
            $rontgen = Rontgen::with('patient.medicalHistory', 'patient.dentalHistory')->find($id);

            if (!$rontgen) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Data rontgen tidak ditemukan'),
                    404
                );
            }

            $data = [
                'id' => $rontgen->id,
                'patient' => [
                    'id' => $rontgen->patient->id,
                    'name' => $rontgen->patient->name,
                    'phone' => $rontgen->patient->phone,
                    'birth_date' => $rontgen->patient->birth_date,
                    'gender' => $rontgen->patient->gender,
                    'medical_history' => $rontgen->patient->medicalHistory ? [
                        'has_allergy' => $rontgen->patient->medicalHistory->has_allergy,
                        'allergy_detail' => $rontgen->patient->medicalHistory->allergy_detail,
                        'has_systemic_disease' => $rontgen->patient->medicalHistory->has_systemic_disease,
                        'systemic_disease_detail' => $rontgen->patient->medicalHistory->systemic_disease_detail,
                        'undergoing_treatment' => $rontgen->patient->medicalHistory->undergoing_treatment,
                        'treatment_detail' => $rontgen->patient->medicalHistory->treatment_detail,
                        'ever_hospitalized' => $rontgen->patient->medicalHistory->ever_hospitalized,
                        'hospitalized_reason' => $rontgen->patient->medicalHistory->hospitalized_reason,
                        'smoking_or_alcohol' => $rontgen->patient->medicalHistory->smoking_or_alcohol,
                    ] : null,
                    'dental_history' => $rontgen->patient->dentalHistory ? [
                        'frequent_tooth_pain' => $rontgen->patient->dentalHistory->frequent_tooth_pain,
                        'tooth_pain_detail' => $rontgen->patient->dentalHistory->tooth_pain_detail,
                        'bleeding_gums' => $rontgen->patient->dentalHistory->bleeding_gums,
                        'ever_dental_treatment' => $rontgen->patient->dentalHistory->ever_dental_treatment,
                        'dental_treatment_detail' => $rontgen->patient->dentalHistory->dental_treatment_detail,
                        'brushing_frequency' => $rontgen->patient->dentalHistory->brushing_frequency,
                        'use_floss_or_mouthwash' => $rontgen->patient->dentalHistory->use_floss_or_mouthwash,
                        'bad_habits' => $rontgen->patient->dentalHistory->bad_habits,
                        'bad_habits_detail' => $rontgen->patient->dentalHistory->bad_habits_detail,
                        'ever_braces' => $rontgen->patient->dentalHistory->ever_braces,
                        'braces_years' => $rontgen->patient->dentalHistory->braces_years,
                        'root_canal_treatment' => $rontgen->patient->dentalHistory->root_canal_treatment,
                        'root_canal_detail' => $rontgen->patient->dentalHistory->root_canal_detail,
                        'dentures' => $rontgen->patient->dentalHistory->dentures,
                        'routine_checkup' => $rontgen->patient->dentalHistory->routine_checkup,
                        'dental_checkup_frequency' => $rontgen->patient->dentalHistory->dental_checkup_frequency,
                        'doctor_notes' => $rontgen->patient->dentalHistory->doctor_notes,
                    ] : null,
                ],
                'xray_image_url' => $this->getRontgenImageUrl($rontgen->xray_image),
                'detail' => $rontgen->detail,
                'created_at' => $rontgen->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $rontgen->updated_at->format('Y-m-d H:i:s'),
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Detail rontgen berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function update(UpdateRontgenRequest $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $rontgen = Rontgen::find($id);

            if (!$rontgen) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Data rontgen tidak ditemukan'),
                    404
                );
            }

            $oldImage = $rontgen->xray_image;

            if ($request->hasFile('xray_image')) {
                $imageName = FileHelper::uploadImage($request->file('xray_image'), 'rontgen');
                if ($imageName) {
                    $rontgen->xray_image = $imageName;
                }
            }

            if ($request->has('detail')) {
                $rontgen->detail = $request->detail;
            }

            $rontgen->save();

            if ($request->hasFile('xray_image') && $oldImage) {
                FileHelper::deleteImage('rontgen/' . $oldImage);
            }

            DB::commit();

            $rontgen->load('patient');

            $data = [
                'id' => $rontgen->id,
                'patient' => [
                    'id' => $rontgen->patient->id,
                    'name' => $rontgen->patient->name,
                ],
                'xray_image_url' => $this->getRontgenImageUrl($rontgen->xray_image),
                'detail' => $rontgen->detail,
                'updated_at' => $rontgen->updated_at->format('Y-m-d H:i:s'),
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Data rontgen berhasil diupdate'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal mengupdate rontgen: ' . $e->getMessage()),
                500
            );
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $rontgen = Rontgen::find($id);

            if (!$rontgen) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Data rontgen tidak ditemukan'),
                    404
                );
            }

            $oldImage = $rontgen->xray_image;

            $rontgen->delete();

            if ($oldImage) {
                FileHelper::deleteImage('rontgen/' . $oldImage);
            }

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, null, 'Data rontgen berhasil dihapus'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menghapus rontgen: ' . $e->getMessage()),
                500
            );
        }
    }

    public function download($id)
    {
        try {
            $rontgen = Rontgen::find($id);

            if (!$rontgen) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Data rontgen tidak ditemukan'),
                    404
                );
            }

            $possiblePaths = [
                'rontgen/' . $rontgen->xray_image,
                'rontgens/' . $rontgen->xray_image,
            ];

            $path = null;
            foreach ($possiblePaths as $candidate) {
                if (Storage::disk('public')->exists($candidate)) {
                    $path = $candidate;
                    break;
                }
            }

            if (!$path) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'File rontgen tidak ditemukan di storage'),
                    404
                );
            }

            return response()->download(storage_path('app/public/' . $path), basename($path));
        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal download rontgen: ' . $e->getMessage()),
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
