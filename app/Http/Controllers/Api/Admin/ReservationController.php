<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminReservationRequest;
use App\Http\Requests\UpdateReservationPatientDetailsRequest;
use App\Models\Reservation;
use App\Models\Patient;
use App\Models\Doctor;
use App\Http\Requests\UpdateReservationStatusRequest;
use App\Helpers\FileHelper;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReservationController extends Controller
{
    public function store(StoreAdminReservationRequest $request)
    {
        DB::beginTransaction();

        try {
            $doctor = Doctor::find($request->doctor_id);

            if (!$doctor || !$this->isDoctorAvailable($doctor, $request->reservation_date, $request->appointment_time)) {
                DB::rollBack();
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Waktu tidak tersedia dalam jadwal dokter'),
                    422
                );
            }

            $patient = null;

            if ($request->patient_category === 'existing') {
                $patientQuery = Patient::query()
                    ->where('name', $request->name)
                    ->where('phone', $request->phone);

                if ($request->filled('birth_date')) {
                    $patientQuery->whereDate('birth_date', $request->birth_date);
                }

                $patient = $patientQuery->first();

                if (!$patient) {
                    DB::rollBack();
                    return response()->json(
                        FileHelper::formatResponse(false, null, 'Pasien lama tidak ditemukan. Silakan pilih kategori pasien baru jika ini pasien pertama kali.'),
                        404
                    );
                }

                $patient->update([
                    'birth_date' => $request->birth_date ?? $patient->birth_date,
                    'age' => $request->age ?? $patient->age,
                ]);
            }

            if ($request->patient_category === 'new') {
                if (Patient::where('phone', $request->phone)->exists()) {
                    DB::rollBack();
                    return response()->json(
                        FileHelper::formatResponse(false, null, 'Nomor telepon sudah terdaftar. Gunakan kategori pasien lama.'),
                        422
                    );
                }

                $patient = Patient::create([
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'gender' => $request->input('gender', 'male'),
                    'address' => $request->input('address', '-'),
                    'birth_date' => $request->birth_date,
                    'age' => $request->age,
                ]);
            }

            $reservation = Reservation::create([
                'patient_id' => $patient->id,
                'patient_category' => $request->patient_category,
                'doctor_id' => $request->doctor_id,
                'complain' => $request->complain,
                'reservation_date' => $request->reservation_date,
                'birth_date' => $request->birth_date,
                'age' => $request->age,
                'appointment_time' => $request->appointment_time,
                'status' => 'validated',
            ]);

            $reservation->services()->attach($request->service_ids);

            DB::commit();

            $reservation->load(['patient', 'doctor', 'services']);

            $data = [
                'id' => $reservation->id,
                'patient' => [
                    'id' => $reservation->patient->id,
                    'name' => $reservation->patient->name,
                    'phone' => $reservation->patient->phone,
                ],
                'patient_category' => $reservation->patient_category,
                'doctor' => [
                    'id' => $reservation->doctor->id,
                    'name' => $reservation->doctor->name,
                ],
                'services' => $reservation->services->map(fn ($service) => [
                    'id' => $service->id,
                    'name' => $service->name,
                ]),
                'complain' => $reservation->complain,
                'reservation_date' => $reservation->reservation_date,
                'appointment_time' => substr((string) $reservation->appointment_time, 0, 5),
                'birth_date' => $reservation->birth_date,
                'age' => $reservation->age,
                'status' => $reservation->status,
                'created_at' => optional($reservation->created_at)->format('Y-m-d H:i:s'),
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Reservasi berhasil dibuat'),
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

    public function index()
    {
        try {
            $reservations = Reservation::with(['patient', 'services', 'doctor'])
                ->latest()
                ->paginate(10);

            $data = [
                'reservations' => $reservations->map(function ($reservation) {
                    return [
                        'id' => $reservation->id,
                        'patient' => [
                            'id' => $reservation->patient->id,
                            'name' => $reservation->patient->name,
                            'phone' => $reservation->patient->phone,
                        ],
                        'services' => $reservation->services->map(function ($service) {
                            return [
                                'id' => $service->id,
                                'name' => $service->name,
                            ];
                        }),
                        'doctor' => [
                            'id' => $reservation->doctor->id,
                            'name' => $reservation->doctor->name,
                        ],
                        'complain' => $reservation->complain,
                        'reservation_date' => $reservation->reservation_date,
                        'appointment_time' => substr($reservation->appointment_time, 0, 5),
                        'birth_date' => $reservation->birth_date,
                        'age' => $reservation->age,
                        'patient_category' => $reservation->patient_category,
                        'status' => $reservation->status,
                        'created_at' => optional($reservation->created_at)->format('Y-m-d H:i:s'),
                    ];
                }),
                'pagination' => [
                    'current_page' => $reservations->currentPage(),
                    'last_page' => $reservations->lastPage(),
                    'per_page' => $reservations->perPage(),
                    'total' => $reservations->total(),
                ],
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Data reservasi berhasil diambil'),
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
            $reservation = Reservation::with(['patient.medicalHistory', 'patient.dentalHistory', 'services', 'doctor'])
                ->find($id);

            if (!$reservation) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Reservasi tidak ditemukan'),
                    404
                );
            }

            $data = [
                'id' => $reservation->id,
                'patient' => [
                    'id' => $reservation->patient->id,
                    'name' => $reservation->patient->name,
                    'phone' => $reservation->patient->phone,
                    'birth_date' => $reservation->patient->birth_date,
                    'gender' => $reservation->patient->gender,
                    'address' => $reservation->patient->address,
                    'medical_history' => $reservation->patient->medicalHistory ? [
                        'has_allergy' => $reservation->patient->medicalHistory->has_allergy,
                        'allergy_detail' => $reservation->patient->medicalHistory->allergy_detail,
                        'has_systemic_disease' => $reservation->patient->medicalHistory->has_systemic_disease,
                        'systemic_disease_detail' => $reservation->patient->medicalHistory->systemic_disease_detail,
                        'undergoing_treatment' => $reservation->patient->medicalHistory->undergoing_treatment,
                        'treatment_detail' => $reservation->patient->medicalHistory->treatment_detail,
                        'ever_hospitalized' => $reservation->patient->medicalHistory->ever_hospitalized,
                        'hospitalized_reason' => $reservation->patient->medicalHistory->hospitalized_reason,
                        'smoking_or_alcohol' => $reservation->patient->medicalHistory->smoking_or_alcohol,
                    ] : null,
                    'dental_history' => $reservation->patient->dentalHistory ? [
                        'frequent_tooth_pain' => $reservation->patient->dentalHistory->frequent_tooth_pain,
                        'tooth_pain_detail' => $reservation->patient->dentalHistory->tooth_pain_detail,
                        'bleeding_gums' => $reservation->patient->dentalHistory->bleeding_gums,
                        'ever_dental_treatment' => $reservation->patient->dentalHistory->ever_dental_treatment,
                        'dental_treatment_detail' => $reservation->patient->dentalHistory->dental_treatment_detail,
                        'brushing_frequency' => $reservation->patient->dentalHistory->brushing_frequency,
                        'use_floss_or_mouthwash' => $reservation->patient->dentalHistory->use_floss_or_mouthwash,
                        'bad_habits' => $reservation->patient->dentalHistory->bad_habits,
                        'bad_habits_detail' => $reservation->patient->dentalHistory->bad_habits_detail,
                        'ever_braces' => $reservation->patient->dentalHistory->ever_braces,
                        'braces_years' => $reservation->patient->dentalHistory->braces_years,
                        'root_canal_treatment' => $reservation->patient->dentalHistory->root_canal_treatment,
                        'root_canal_detail' => $reservation->patient->dentalHistory->root_canal_detail,
                        'dentures' => $reservation->patient->dentalHistory->dentures,
                        'routine_checkup' => $reservation->patient->dentalHistory->routine_checkup,
                        'dental_checkup_frequency' => $reservation->patient->dentalHistory->dental_checkup_frequency,
                    ] : null,
                ],
                'patient_form' => [
                    'patient_id' => $reservation->patient->id,
                    'name' => $reservation->patient->name,
                    'nickname' => $reservation->patient->nickname,
                    'gender' => $reservation->patient->gender,
                    'age' => $reservation->patient->age,
                    'birth_place' => $reservation->patient->birth_place,
                    'birth_date' => $reservation->patient->birth_date,
                    'address' => $reservation->patient->address,
                    'village' => $reservation->patient->village,
                    'district' => $reservation->patient->district,
                    'city' => $reservation->patient->city,
                    'phone' => $reservation->patient->phone,
                    'occupation' => $reservation->patient->occupation,
                    'parent_name' => $reservation->patient->parent_name,
                    'height' => $reservation->patient->height,
                    'weight' => $reservation->patient->weight,
                ],
                'medical_history_form' => [
                    'has_allergy' => optional($reservation->patient->medicalHistory)->has_allergy,
                    'allergy_detail' => optional($reservation->patient->medicalHistory)->allergy_detail,
                    'has_systemic_disease' => optional($reservation->patient->medicalHistory)->has_systemic_disease,
                    'systemic_disease_detail' => optional($reservation->patient->medicalHistory)->systemic_disease_detail,
                    'undergoing_treatment' => optional($reservation->patient->medicalHistory)->undergoing_treatment,
                    'treatment_detail' => optional($reservation->patient->medicalHistory)->treatment_detail,
                    'ever_hospitalized' => optional($reservation->patient->medicalHistory)->ever_hospitalized,
                    'hospitalized_reason' => optional($reservation->patient->medicalHistory)->hospitalized_reason,
                    'smoking_or_alcohol' => optional($reservation->patient->medicalHistory)->smoking_or_alcohol,
                ],
                'dental_history_form' => [
                    'frequent_tooth_pain' => optional($reservation->patient->dentalHistory)->frequent_tooth_pain,
                    'tooth_pain_detail' => optional($reservation->patient->dentalHistory)->tooth_pain_detail,
                    'bleeding_gums' => optional($reservation->patient->dentalHistory)->bleeding_gums,
                    'ever_dental_treatment' => optional($reservation->patient->dentalHistory)->ever_dental_treatment,
                    'dental_treatment_detail' => optional($reservation->patient->dentalHistory)->dental_treatment_detail,
                    'brushing_frequency' => optional($reservation->patient->dentalHistory)->brushing_frequency,
                    'use_floss_or_mouthwash' => optional($reservation->patient->dentalHistory)->use_floss_or_mouthwash,
                    'bad_habits' => optional($reservation->patient->dentalHistory)->bad_habits,
                    'bad_habits_detail' => optional($reservation->patient->dentalHistory)->bad_habits_detail,
                    'ever_braces' => optional($reservation->patient->dentalHistory)->ever_braces,
                    'braces_years' => optional($reservation->patient->dentalHistory)->braces_years,
                    'root_canal_treatment' => optional($reservation->patient->dentalHistory)->root_canal_treatment,
                    'root_canal_detail' => optional($reservation->patient->dentalHistory)->root_canal_detail,
                    'dentures' => optional($reservation->patient->dentalHistory)->dentures,
                    'routine_checkup' => optional($reservation->patient->dentalHistory)->routine_checkup,
                    'dental_checkup_frequency' => optional($reservation->patient->dentalHistory)->dental_checkup_frequency,
                    'doctor_notes' => optional($reservation->patient->dentalHistory)->doctor_notes,
                ],
                'services' => $reservation->services->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'detail' => $service->detail,
                    ];
                }),
                'doctor' => [
                    'id' => $reservation->doctor->id,
                    'name' => $reservation->doctor->name,
                    'specialization' => $reservation->doctor->specialization,
                ],
                'complain' => $reservation->complain,
                'reservation_date' => $reservation->reservation_date,
                'appointment_time' => substr($reservation->appointment_time, 0, 5),
                'birth_date' => $reservation->birth_date,
                'age' => $reservation->age,
                'patient_category' => $reservation->patient_category,
                'status' => $reservation->status,
                'created_at' => optional($reservation->created_at)->format('Y-m-d H:i:s'),
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Detail reservasi berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function update(UpdateReservationStatusRequest $request, $id)
    {
        try {
            $reservation = Reservation::find($id);

            if (!$reservation) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Reservasi tidak ditemukan'),
                    404
                );
            }

            if ($request->has('status')) {
                $reservation->status = $request->status;
            }

            $reservation->save();

            $data = [
                'id' => $reservation->id,
                'status' => $reservation->status,
                'created_at' => optional($reservation->created_at)->format('Y-m-d H:i:s'),
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Status reservasi berhasil diupdate'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal mengupdate reservasi: ' . $e->getMessage()),
                500
            );
        }
    }

    public function updatePatientDetails(UpdateReservationPatientDetailsRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $reservation = Reservation::with('patient')->find($id);

            if (!$reservation) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Reservasi tidak ditemukan'),
                    404
                );
            }

            if ((int) $reservation->patient_id !== (int) $request->patient_id) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Patient ID tidak cocok dengan reservasi'),
                    422
                );
            }

            $patientPayload = $request->only([
                'name', 'nickname', 'gender', 'age', 'birth_place', 'birth_date',
                'address', 'village', 'district', 'city', 'phone', 'occupation',
                'parent_name', 'height', 'weight',
            ]);

            $reservation->patient->update($patientPayload);

            if ($request->has('medical_history')) {
                $reservation->patient->medicalHistory()->updateOrCreate(
                    ['patient_id' => $reservation->patient_id],
                    $request->input('medical_history', [])
                );
            }

            if ($request->has('dental_history')) {
                $reservation->patient->dentalHistory()->updateOrCreate(
                    ['patient_id' => $reservation->patient_id],
                    $request->input('dental_history', [])
                );
            }

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, null, 'Data pasien pada reservasi berhasil disimpan'),
                200
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menyimpan data pasien reservasi: ' . $e->getMessage()),
                500
            );
        }
    }

    public function destroy($id)
    {
        try {
            $reservation = Reservation::find($id);

            if (!$reservation) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Reservasi tidak ditemukan'),
                    404
                );
            }

            $reservation->delete();

            return response()->json(
                FileHelper::formatResponse(true, null, 'Reservasi berhasil dihapus'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menghapus reservasi: ' . $e->getMessage()),
                500
            );
        }
    }

    private function isDoctorAvailable(Doctor $doctor, string $date, string $time): bool
    {
        $schedule = is_array($doctor->schedule) ? $doctor->schedule : [];
        $dayName = strtolower(Carbon::parse($date)->englishDayOfWeek);
        $dayMap = [
            'monday' => 'senin',
            'tuesday' => 'selasa',
            'wednesday' => 'rabu',
            'thursday' => 'kamis',
            'friday' => 'jumat',
            'saturday' => 'sabtu',
            'sunday' => 'minggu',
        ];

        $localizedDayName = $dayMap[$dayName] ?? $dayName;
        $sessions = $schedule[$dayName] ?? $schedule[$localizedDayName] ?? [];

        if (empty($sessions)) {
            return false;
        }

        $appointment = Carbon::createFromFormat('H:i', $time);

        foreach ($sessions as $session) {
            [$start, $end] = explode('-', $session);
            $startTime = Carbon::createFromFormat('H:i', $start);
            $endTime = Carbon::createFromFormat('H:i', $end);

            if ($appointment->betweenIncluded($startTime, $endTime)) {
                return true;
            }
        }

        return false;
    }
}
