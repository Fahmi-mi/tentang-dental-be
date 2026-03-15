<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Rontgen;
use App\Helpers\FileHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $today = now()->toDateString();
            
            $dailyStats = [
                'pending' => Reservation::whereDate('reservation_date', $today)
                    ->where('status', 'pending')
                    ->count(),
                'validated' => Reservation::whereDate('reservation_date', $today)
                    ->where('status', 'validated')
                    ->count(),
                'completed' => Reservation::whereDate('reservation_date', $today)
                    ->where('status', 'completed')
                    ->count(),
                'total' => Reservation::whereDate('reservation_date', $today)->count(),
            ];

            $totals = [
                'total_patients' => Patient::count(),
                'total_reservations' => Reservation::count(),
                'total_rontgens' => Rontgen::count(),
                'pending_reservations' => Reservation::where('status', 'pending')->count(),
            ];

            $monthlyAnalytics = Reservation::select(
                    'services.name as service_name',
                    DB::raw('COUNT(reservations.id) as total_reservations')
                )
                ->join('reservation_service', 'reservations.id', '=', 'reservation_service.reservation_id')
                ->join('services', 'reservation_service.service_id', '=', 'services.id')
                ->whereYear('reservations.reservation_date', now()->year)
                ->whereMonth('reservations.reservation_date', now()->month)
                ->groupBy('services.id', 'services.name')
                ->orderByDesc('total_reservations')
                ->get();

            $recentReservations = Reservation::with(['patient', 'services', 'doctor'])
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($reservation) {
                    return [
                        'id' => $reservation->id,
                        'patient_name' => optional($reservation->patient)->name,
                        'service_name' => optional($reservation->services->first())->name,
                        'doctor_name' => optional($reservation->doctor)->name,
                        'reservation_date' => $reservation->reservation_date,
                        'appointment_time' => substr((string) $reservation->appointment_time, 0, 5),
                        'status' => $reservation->status,
                    ];
                });

            $data = [
                'daily_statistics' => $dailyStats,
                'totals' => $totals,
                'pending_reservations' => $dailyStats['pending'],
                'validated_reservations' => $dailyStats['validated'],
                'completed_reservations' => $dailyStats['completed'],
                'total_patients' => $totals['total_patients'],
                'monthly_analytics' => $monthlyAnalytics->map(function ($item) {
                    return [
                        'service_name' => $item->service_name,
                        'total_reservations' => $item->total_reservations,
                    ];
                }),
                'recent_reservations' => $recentReservations,
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Data dashboard berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function reservationStats(Request $request)
    {
        try {
            $month = $request->input('month', now()->format('Y-m'));
            $startDate = $request->input('start_date', $month . '-01');
            $endDate = $request->input('end_date', Carbon::parse($startDate)->endOfMonth()->toDateString());

            $stats = Reservation::whereBetween('reservation_date', [$startDate, $endDate])
                ->select(
                    'status',
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status');

            $byDate = Reservation::whereBetween('reservation_date', [$startDate, $endDate])
                ->select('reservation_date', DB::raw('COUNT(*) as total'))
                ->groupBy('reservation_date')
                ->orderBy('reservation_date')
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => (string) $item->reservation_date,
                        'total' => (int) $item->total,
                    ];
                });

            $data = [
                'month' => $month,
                'total_reservations' => (int) $stats->sum(),
                'by_status' => [
                    'pending' => (int) ($stats['pending'] ?? 0),
                    'validated' => (int) ($stats['validated'] ?? 0),
                    'completed' => (int) ($stats['completed'] ?? 0),
                    'cancelled' => (int) ($stats['cancelled'] ?? 0),
                ],
                'by_date' => $byDate,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'statistics' => [
                    'pending' => (int) ($stats['pending'] ?? 0),
                    'validated' => (int) ($stats['validated'] ?? 0),
                    'completed' => (int) ($stats['completed'] ?? 0),
                    'cancelled' => (int) ($stats['cancelled'] ?? 0),
                    'total' => (int) $stats->sum(),
                ],
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Statistik reservasi berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function serviceAnalytics(Request $request)
    {
        try {
            $month = $request->input('month', now()->format('Y-m'));
            $startDate = $request->input('start_date', $month . '-01');
            $endDate = $request->input('end_date', Carbon::parse($startDate)->endOfMonth()->toDateString());

            $analytics = Reservation::select(
                    'services.id',
                    'services.name',
                    DB::raw('COUNT(reservations.id) as total_reservations')
                )
                ->join('reservation_service', 'reservations.id', '=', 'reservation_service.reservation_id')
                ->join('services', 'reservation_service.service_id', '=', 'services.id')
                ->whereBetween('reservations.reservation_date', [$startDate, $endDate])
                ->groupBy('services.id', 'services.name')
                ->orderByDesc('total_reservations')
                ->get();

            $data = [
                'month' => $month,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'services' => $analytics->map(function ($item) {
                    return [
                        'service_id' => $item->id,
                        'service_name' => $item->name,
                        'reservation_count' => (int) $item->total_reservations,
                        'total_reservations' => (int) $item->total_reservations,
                    ];
                }),
                'summary' => [
                    'total_reservations' => (int) $analytics->sum('total_reservations'),
                ],
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Analitik layanan berhasil diambil'),
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
