<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\FormatsApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ReservationDetailResource;
use App\Http\Resources\Admin\ReservationListResource;
use App\Http\Resources\Admin\ReservationStatusResource;
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
    use FormatsApiResponse;

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

            return response()->json(
                FileHelper::formatResponse(true, new ReservationListResource($reservation), 'Reservasi berhasil dibuat'),
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
            return $this->paginatedResourceResponse(
                $reservations,
                'reservations',
                ReservationListResource::collection($reservations->getCollection())->resolve(),
                'Data reservasi berhasil diambil'
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

            return response()->json(
                FileHelper::formatResponse(true, new ReservationDetailResource($reservation), 'Detail reservasi berhasil diambil'),
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

            return response()->json(
                FileHelper::formatResponse(true, new ReservationStatusResource($reservation), 'Status reservasi berhasil diupdate'),
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
