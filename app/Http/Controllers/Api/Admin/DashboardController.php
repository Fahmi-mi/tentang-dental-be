<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Rontgen;
use App\Helpers\FileHelper;
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

            $currentMonth = now()->format('Y-m');
            
            $monthlyAnalytics = Reservation::select(
                    'services.name as service_name',
                    DB::raw('COUNT(reservations.id) as total_reservations'),
                    DB::raw('SUM(services.price) as total_revenue')
                )
                ->join('reservation_service', 'reservations.id', '=', 'reservation_service.reservation_id')
                ->join('services', 'reservation_service.service_id', '=', 'services.id')
                ->whereYear('reservations.reservation_date', now()->year)
                ->whereMonth('reservations.reservation_date', now()->month)
                ->groupBy('services.id', 'services.name')
                ->orderByDesc('total_reservations')
                ->get();

            $recentReservations = Reservation::with(['patient', 'service', 'doctor'])
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($reservation) {
                    return [
                        'id' => $reservation->id,
                        'patient_name' => $reservation->patient->name,
                        'service_name' => $reservation->service->name,
                        'doctor_name' => $reservation->doctor->name,
                        'reservation_date' => $reservation->reservation_date,
                        'reservation_time' => substr($reservation->reservation_time, 0, 5),
                        'status' => $reservation->status,
                    ];
                });

            $data = [
                'daily_statistics' => $dailyStats,
                'totals' => $totals,
                'monthly_analytics' => $monthlyAnalytics->map(function ($item) {
                    return [
                        'service_name' => $item->service_name,
                        'total_reservations' => $item->total_reservations,
                        'total_revenue' => (float) $item->total_revenue,
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
            $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

            $stats = Reservation::whereBetween('reservation_date', [$startDate, $endDate])
                ->select(
                    'status',
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status');

            $data = [
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'statistics' => [
                    'pending' => $stats['pending'] ?? 0,
                    'validated' => $stats['validated'] ?? 0,
                    'completed' => $stats['completed'] ?? 0,
                    'cancelled' => $stats['cancelled'] ?? 0,
                    'total' => $stats->sum(),
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
            $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

            $analytics = Reservation::select(
                    'services.id',
                    'services.name',
                    'services.price',
                    DB::raw('COUNT(reservations.id) as total_reservations'),
                    DB::raw('SUM(services.price) as total_revenue')
                )
                ->join('services', 'reservations.service_id', '=', 'services.id')
                ->whereBetween('reservations.reservation_date', [$startDate, $endDate])
                ->groupBy('services.id', 'services.name', 'services.price')
                ->orderByDesc('total_reservations')
                ->get();

            $data = [
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'services' => $analytics->map(function ($item) {
                    return [
                        'service_id' => $item->id,
                        'service_name' => $item->name,
                        'service_price' => (float) $item->price,
                        'total_reservations' => $item->total_reservations,
                        'total_revenue' => (float) $item->total_revenue,
                    ];
                }),
                'summary' => [
                    'total_reservations' => $analytics->sum('total_reservations'),
                    'total_revenue' => (float) $analytics->sum('total_revenue'),
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
