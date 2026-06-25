<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\AppointmentsExport;
use App\Models\Appointment;
use App\Models\Commission;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'year');
        $startInput = $request->get('start', '');
        $endInput = $request->get('end', '');
        $userId = $request->get('user_id');
        $statusFilter = $request->get('status', 'completed');

        [$startDate, $endDate] = $this->resolveDateRange($period, $startInput, $endInput);

        $monthlyRevenue = [];
        $monthlyAppointments = [];
        $cursor = $startDate->copy()->startOfMonth();
        $endMonth = $endDate->copy()->startOfMonth();
        while ($cursor <= $endMonth) {
            $monthStart = $cursor->copy()->startOfMonth();
            $monthEnd = $cursor->copy()->endOfMonth();

            $base = Appointment::where('status', $statusFilter)
                ->whereBetween('start', [$monthStart, $monthEnd]);

            if ($userId) {
                $base->where('user_id', $userId);
            }

            $revenue = (clone $base)
                ->join('appointment_service', 'appointments.id', '=', 'appointment_service.appointment_id')
                ->sum('appointment_service.price');

            $count = (clone $base)->count();

            $monthlyRevenue[] = [
                'label' => $cursor->locale('pt-BR')->isoFormat('MMM/YY'),
                'value' => (float) $revenue,
            ];

            $monthlyAppointments[] = [
                'label' => $cursor->locale('pt-BR')->isoFormat('MMM/YY'),
                'value' => $count,
            ];

            $cursor->addMonth();
        }

        $topServices = Appointment::where('appointments.status', $statusFilter)
            ->whereBetween('appointments.start', [$startDate, $endDate])
            ->when($userId, fn($q) => $q->where('appointments.user_id', $userId))
            ->join('appointment_service', 'appointments.id', '=', 'appointment_service.appointment_id')
            ->join('services', 'appointment_service.service_id', '=', 'services.id')
            ->select('services.name', 'services.id', DB::raw('COUNT(*) as total'), DB::raw('SUM(appointment_service.price) as total_price'))
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $commissions = Commission::with('user')
            ->selectRaw('commissions.user_id, SUM(commissions.value) as total, COUNT(*) as count, SUM(CASE WHEN commissions.paid THEN commissions.value ELSE 0 END) as paid_total')
            ->whereHas('appointment', fn($q) => $q
                ->whereBetween('start', [$startDate, $endDate])
                ->when($userId, fn($q) => $q->where('user_id', $userId))
            )
            ->groupBy('commissions.user_id')
            ->get();

        $paymentMethods = Payment::selectRaw('payments.method, SUM(payments.amount) as total, COUNT(*) as count')
            ->whereHas('appointment', fn($q) => $q
                ->whereBetween('start', [$startDate, $endDate])
                ->when($userId, fn($q) => $q->where('user_id', $userId))
            )
            ->groupBy('payments.method')
            ->get();

        $totalRevenue = Appointment::where('appointments.status', $statusFilter)
            ->whereBetween('appointments.start', [$startDate, $endDate])
            ->when($userId, fn($q) => $q->where('appointments.user_id', $userId))
            ->join('appointment_service', 'appointments.id', '=', 'appointment_service.appointment_id')
            ->sum('appointment_service.price');

        $totalAppointments = Appointment::where('status', $statusFilter)
            ->whereBetween('start', [$startDate, $endDate])
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->count();

        $monthsInRange = max(1, $startDate->diffInMonths($endDate) + 1);
        $avgPerMonth = $totalAppointments > 0 ? round($totalAppointments / $monthsInRange, 1) : 0;

        $users = User::where('active', true)->orderBy('name')->get();

        return view('admin.reports.index', compact(
            'monthlyRevenue', 'monthlyAppointments', 'topServices',
            'commissions', 'paymentMethods', 'totalRevenue',
            'totalAppointments', 'avgPerMonth', 'users',
            'period', 'startInput', 'endInput', 'userId', 'statusFilter'
        ));
    }

    private function resolveDateRange($period, $start, $end)
    {
        return match ($period) {
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'last_month' => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()],
            'quarter' => [Carbon::now()->subMonths(3)->startOfMonth(), Carbon::now()->endOfMonth()],
            'all' => [
                Appointment::min('start') ? Carbon::parse(Appointment::min('start'))->startOfDay() : Carbon::now()->startOfYear(),
                Carbon::now()->endOfDay(),
            ],
            'custom' => [
                $start ? Carbon::parse($start)->startOfDay() : Carbon::now()->startOfMonth(),
                $end ? Carbon::parse($end)->endOfDay() : Carbon::now()->endOfDay(),
            ],
            default => [Carbon::now()->subMonths(11)->startOfMonth(), Carbon::now()->endOfMonth()],
        };
    }

    public function exportCsv(Request $request)
    {
        $start = $request->get('start') ? Carbon::parse($request->get('start'))->startOfDay() : null;
        $end = $request->get('end') ? Carbon::parse($request->get('end'))->endOfDay() : null;
        $userId = $request->get('user_id');
        $status = $request->get('status');

        $export = new AppointmentsExport();
        $csv = $export->exportCsv($start, $end, $userId, $status);

        $filename = 'agendamentos-' . now()->format('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
