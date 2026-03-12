<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientMedicalHistory;
use App\Models\PatientDentalHistory;
use App\Models\Reservation;
use App\Http\Requests\StoreReservationNewPatientRequest;
use App\Http\Requests\StoreReservationExistingPatientRequest;
use App\Helpers\FileHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function storeNewPatient(StoreReservationNewPatientRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $patient = Patient::create([
                'name' => $request->name,
                'nickname' => $request->nickname,
                'gender' => $request->gender,
                'age' => $request->age,
                'birth_place' => $request->birth_place,
                'birth_date' => $request->birth_date,
                'address' => $request->address,
                'village' => $request->village,
                'district' => $request->district,
                'city' => $request->city,
                'phone' => $request->phone,
                'occupation' => $request->occupation,
                'parent_name' => $request->parent_name,
                'height' => $request->height,
                'weight' => $request->weight,
            ]);

            PatientMedicalHistory::create([
                'patient_id' => $patient->id,
                'has_allergy' => $request->has_allergy,
                'allergy_detail' => $request->allergy_detail,
                'has_systemic_disease' => $request->has_systemic_disease,
                'systemic_disease_detail' => $request->systemic_disease_detail,
                'undergoing_treatment' => $request->undergoing_treatment,
                'treatment_detail' => $request->treatment_detail,
                'ever_hospitalized' => $request->ever_hospitalized,
                'hospitalized_reason' => $request->hospitalized_reason,
                'smoking_or_alcohol' => $request->smoking_or_alcohol,
            ]);

            PatientDentalHistory::create([
                'patient_id' => $patient->id,
                'frequent_tooth_pain' => $request->frequent_tooth_pain,
                'tooth_pain_detail' => $request->tooth_pain_detail,
                'bleeding_gums' => $request->bleeding_gums,
                'ever_dental_treatment' => $request->ever_dental_treatment,
                'dental_treatment_detail' => $request->dental_treatment_detail,
                'brushing_frequency' => $request->brushing_frequency,
                'use_floss_or_mouthwash' => $request->use_floss_or_mouthwash,
                'bad_habits' => $request->bad_habits,
                'bad_habits_detail' => $request->bad_habits_detail,
                'ever_braces' => $request->ever_braces,
                'braces_years' => $request->braces_years,
                'root_canal_treatment' => $request->root_canal_treatment,
                'root_canal_detail' => $request->root_canal_detail,
                'dentures' => $request->dentures,
                'routine_checkup' => $request->routine_checkup,
                'dental_checkup_frequency' => $request->dental_checkup_frequency,
            ]);

            $reservation = Reservation::create([
                'patient_id' => $patient->id,
                'doctor_id' => $request->doctor_id,
                'complain' => $request->complain,
                'reservation_date' => $request->reservation_date,
                'appointment_time' => $request->appointment_time,
                'status' => 'pending',
            ]);

            // Attach Services (max 3)
            $reservation->services()->attach($request->service_ids);

            DB::commit();

            $data = [
                'reservation_id' => $reservation->id,
                'patient_id' => $patient->id,
                'patient_name' => $patient->name,
                'reservation_date' => $reservation->reservation_date,
                'appointment_time' => $reservation->appointment_time,
                'status' => $reservation->status,
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Reservasi berhasil dibuat. Silakan datang sesuai jadwal yang dipilih.'),
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal membuat reservasi: ' . $e->getMessage()),
                500
            );
        }
    }

    public function storeExistingPatient(StoreReservationExistingPatientRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $patient = Patient::find($request->patient_id);
            
            if (!$patient) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Data pasien tidak ditemukan'),
                    404
                );
            }

            $reservation = Reservation::create([
                'patient_id' => $request->patient_id,
                'doctor_id' => $request->doctor_id,
                'complain' => $request->complain,
                'reservation_date' => $request->reservation_date,
                'appointment_time' => $request->appointment_time,
                'status' => 'pending',
            ]);

            // Attach Services (max 3)
            $reservation->services()->attach($request->service_ids);

            DB::commit();

            $data = [
                'reservation_id' => $reservation->id,
                'patient_id' => $patient->id,
                'patient_name' => $patient->name,
                'reservation_date' => $reservation->reservation_date,
                'appointment_time' => $reservation->appointment_time,
                'status' => $reservation->status,
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Reservasi berhasil dibuat. Silakan datang sesuai jadwal yang dipilih.'),
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal membuat reservasi: ' . $e->getMessage()),
                500
            );
        }
    }

    public function checkPatient(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
        ]);

        try {
            $patient = Patient::where('phone', $request->phone)
                ->select('id', 'name', 'phone', 'gender', 'age')
                ->first();

            if ($patient) {
                return response()->json(
                    FileHelper::formatResponse(true, [
                        'exists' => true,
                        'patient' => $patient,
                    ], 'Pasien ditemukan'),
                    200
                );
            } else {
                return response()->json(
                    FileHelper::formatResponse(true, [
                        'exists' => false,
                        'patient' => null,
                    ], 'Pasien tidak ditemukan. Silakan registrasi sebagai pasien baru.'),
                    200
                );
            }
        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }
}