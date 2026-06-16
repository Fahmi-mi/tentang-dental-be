<?php

namespace App\Http\Controllers\Api\Public;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReservationAvailableSlotsRequest;
use App\Http\Requests\StorePublicReservationRequest;
use App\Http\Resources\Admin\ReservationListResource;
use App\Http\Resources\Public\ReservationAvailableSlotsResource;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

			if (!$this->isSlotAvailable($doctor, $request->reservation_date, $request->appointment_time)) {
				DB::rollBack();

				return response()->json(
					FileHelper::formatResponse(false, null, 'Slot jam reservasi sudah terisi atau tidak tersedia'),
					422
				);
			}

			$patient = null;

			if ($request->patient_category === 'existing') {
				$patient = Patient::find($request->patient_id);

				if (!$patient) {
					DB::rollBack();

					return response()->json(
						FileHelper::formatResponse(false, null, 'Nomor pasien tidak ditemukan. Pastikan nomor pasien benar atau gunakan kategori pasien baru.'),
						404
					);
				}

				$patient->update([
					'name' => $request->name,
					'phone' => $request->phone,
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

	public function availableSlots(ReservationAvailableSlotsRequest $request)
	{
		try {
			$doctor = Doctor::find($request->doctor_id);
			if (!$doctor) {
				return response()->json(
					FileHelper::formatResponse(false, null, 'Dokter tidak ditemukan'),
					404
				);
			}

			$slots = $this->getAvailableSlots($doctor, $request->reservation_date);

			return response()->json(
				FileHelper::formatResponse(true, new ReservationAvailableSlotsResource(['slots' => $slots]), 'Slot tersedia berhasil diambil'),
				200
			);
		} catch (\Exception $e) {
			return response()->json(
				FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
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
			}

			return response()->json(
				FileHelper::formatResponse(true, [
					'exists' => false,
					'patient' => null,
				], 'Pasien tidak ditemukan. Silakan registrasi sebagai pasien baru.'),
				200
			);
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
		$appointmentTime = Carbon::createFromFormat('H:i', $time)->format('H:i');
		$timeRanges = [];

		if ($this->isAssocArray($schedule)) {
			$dayRanges = $schedule[$localizedDayName] ?? $schedule[$dayName] ?? [];
			if (is_string($dayRanges)) {
				$dayRanges = [$dayRanges];
			}

			if (is_array($dayRanges)) {
				foreach ($dayRanges as $range) {
					if (is_string($range)) {
						$timeRanges[] = $range;
					}
				}
			}
		} else {
			foreach ($schedule as $scheduleItem) {
				if (!is_string($scheduleItem)) {
					continue;
				}

				if (
					stripos($scheduleItem, $localizedDayName) !== false ||
					stripos($scheduleItem, $dayName) !== false
				) {
					$timeRanges[] = $scheduleItem;
				}
			}
		}

		foreach ($timeRanges as $rangeText) {
			[$startTime, $endTime] = $this->extractTimeRange($rangeText);
			if (!$startTime || !$endTime) {
				continue;
			}

			if ($appointmentTime >= $startTime && $appointmentTime <= $endTime) {
				return true;
			}
		}

		return false;
	}

	private function extractTimeRange(string $text): array
	{
		if (!preg_match('/(\d{1,2})[\.:](\d{2})\s*-\s*(\d{1,2})[\.:](\d{2})/', $text, $matches)) {
			return [null, null];
		}

		$startHour = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
		$startMin = $matches[2];
		$endHour = str_pad($matches[3], 2, '0', STR_PAD_LEFT);
		$endMin = $matches[4];

		return ["$startHour:$startMin", "$endHour:$endMin"];
	}

	private function isSlotAvailable(Doctor $doctor, string $date, string $time): bool
	{
		$slots = $this->getAvailableSlots($doctor, $date);
		$availableStarts = array_map(fn ($slot) => $slot['start_time'], $slots);
		return in_array($time, $availableStarts, true);
	}

	private function getAvailableSlots(Doctor $doctor, string $date): array
	{
		$dayRanges = $this->getDoctorDayRanges($doctor->schedule, $date);
		if (empty($dayRanges)) {
			return [];
		}

		$slots = $this->buildHourlySlots($dayRanges);
		$availableStarts = $this->filterReservedSlots($doctor->id, $date, $slots);
		$now = Carbon::now(config('app.timezone'));
		$targetDate = Carbon::parse($date, config('app.timezone'));

		return array_values(array_filter($slots, function ($slot) use ($availableStarts, $now, $targetDate) {
			if (!in_array($slot['start_time'], $availableStarts, true)) {
				return false;
			}

			if ($targetDate->isSameDay($now)) {
				$slotStart = Carbon::parse($targetDate->format('Y-m-d') . ' ' . $slot['start_time'], config('app.timezone'));
				return $slotStart->gt($now);
			}

			return $targetDate->isAfter($now->copy()->startOfDay());
		}));
	}

	private function filterReservedSlots(int $doctorId, string $date, array $slots): array
	{
		$reservedTimes = Reservation::query()
			->where('doctor_id', $doctorId)
			->whereDate('reservation_date', $date)
			->where('status', '!=', 'cancelled')
			->pluck('appointment_time')
			->map(fn ($time) => substr((string) $time, 0, 5))
			->all();

		return array_values(array_filter(array_map(fn ($slot) => $slot['start_time'], $slots), function ($startTime) use ($reservedTimes) {
			return !in_array($startTime, $reservedTimes, true);
		}));
	}

	private function buildHourlySlots(array $ranges): array
	{
		$slots = [];
		foreach ($ranges as $range) {
			[$startTime, $endTime] = $this->extractTimeRange($range);
			if (!$startTime || !$endTime) {
				continue;
			}

			$cursor = Carbon::createFromFormat('H:i', $startTime, config('app.timezone'));
			$end = Carbon::createFromFormat('H:i', $endTime, config('app.timezone'));

			while ($cursor->lt($end)) {
				$next = $cursor->copy()->addHour();
				if ($next->gt($end)) {
					break;
				}

				$slots[] = [
					'start_time' => $cursor->format('H:i'),
					'end_time' => $next->format('H:i'),
					'label' => $cursor->format('H:i') . ' - ' . $next->format('H:i'),
				];

				$cursor = $next;
			}
		}

		return $slots;
	}

	private function getDoctorDayRanges($schedule, string $date): array
	{
		if (is_string($schedule)) {
			$decoded = json_decode($schedule, true);
			$schedule = is_array($decoded) ? $decoded : [];
		} else {
			$schedule = is_array($schedule) ? $schedule : [];
		}

		if (empty($schedule)) {
			return [];
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

		if ($this->isAssocArray($schedule)) {
			$dayRanges = $schedule[$localizedDayName] ?? $schedule[$dayName] ?? [];
			if (is_string($dayRanges)) {
				return [$dayRanges];
			}
			return array_values(array_filter($dayRanges, 'is_string'));
		}

		$matches = [];
		foreach ($schedule as $scheduleItem) {
			if (!is_string($scheduleItem)) {
				continue;
			}
			if (
				stripos($scheduleItem, $localizedDayName) !== false ||
				stripos($scheduleItem, $dayName) !== false
			) {
				$matches[] = $scheduleItem;
			}
		}

		return $matches;
	}

	private function isAssocArray(array $array): bool
	{
		if ($array === []) {
			return false;
		}

		return array_keys($array) !== range(0, count($array) - 1);
	}
}