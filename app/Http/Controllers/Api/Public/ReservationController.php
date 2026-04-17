<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePublicReservationRequest;
use App\Http\Resources\Admin\ReservationListResource;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Reservation;
use App\Helpers\FileHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
public function store(StorePublicReservationRequest $request)
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
FileHelper::formatResponse(false, null, 'Pasien lama tidak ditemukan. Silakan gunakan kategori pasien baru jika ini kunjungan pertama.'),
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
'status' => 'pending',
]);

$reservation->services()->attach($request->service_ids);

DB::commit();

$reservation->load(['patient', 'doctor', 'services']);

return response()->json(
FileHelper::formatResponse(true, new ReservationListResource($reservation), 'Reservasi berhasil dibuat dan menunggu konfirmasi admin.'),
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

private function isDoctorAvailable(Doctor $doctor, string $date, string $time): bool
{
$schedule = $doctor->schedule;

// Handle double-encoded JSON
if (is_string($schedule)) {
$decoded = json_decode($schedule, true);
$schedule = is_array($decoded) ? $decoded : [];
} else {
$schedule = is_array($schedule) ? $schedule : [];
}

if (empty($schedule)) {
return false;
}

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
$appointment = Carbon::createFromFormat('H:i', $time);

// Format: array of strings like "Kamis 08.00 - 16.00"
foreach ($schedule as $scheduleItem) {
// Cek apakah string schedule memuat nama hari
if (stripos($scheduleItem, $localizedDayName) !== false) {
// Extract time range: "Kamis 08.00 - 16.00" → "08.00 - 16.00"
if (preg_match('/(\d{1,2})\.(\d{2})\s*-\s*(\d{1,2})\.(\d{2})/', $scheduleItem, $matches)) {
$startHour = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
$startMin = $matches[2];
$endHour = str_pad($matches[3], 2, '0', STR_PAD_LEFT);
$endMin = $matches[4];

$startTime = Carbon::createFromFormat('H:i', "$startHour:$startMin");
$endTime = Carbon::createFromFormat('H:i', "$endHour:$endMin");

if ($appointment->betweenIncluded($startTime, $endTime)) {
return true;
}
}
}
}

return false;
}
}