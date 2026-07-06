<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Commission;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'today');

        $dateRange = match ($period) {
            'today'    => [Carbon::today(), Carbon::today()->endOfDay()],
            'week'     => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'month'    => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            default    => [Carbon::today(), Carbon::today()->endOfDay()],
        };

        $periodStart = $dateRange[0];
        $periodEnd = $dateRange[1];

        $isAdmin = auth()->user()->isAdmin();

        // --- Queries base ---
        $baseAppointments = Appointment::whereBetween('start', [$periodStart, $periodEnd]);

        $completed = (clone $baseAppointments)->where('status', 'completed');
        $cancelled = (clone $baseAppointments)->whereIn('status', ['cancelled', 'no_show']);
        $pending = (clone $baseAppointments)->whereIn('status', ['scheduled', 'confirmed']);

        // --- Revenue (via pivot) — cached 5min ---
        $revenue = (float) Cache::remember("rev_{$period}_{$periodStart}", 300, fn() =>
            (clone $completed)
                ->join('appointment_service', 'appointments.id', '=', 'appointment_service.appointment_id')
                ->sum('appointment_service.price')
        );

        $salesTotal = (float) Cache::remember("sales_{$period}_{$periodStart}", 300, fn() =>
            Sale::whereBetween('created_at', [$periodStart, $periodEnd])->sum('total')
        );
        $salesCount = Cache::remember("sales_count_{$period}_{$periodStart}", 300, fn() =>
            Sale::whereBetween('created_at', [$periodStart, $periodEnd])->count()
        );

        $totalRevenue = $revenue + $salesTotal;

        // --- Receita Dia/Semana/Mês (cached 5min) ---
        $todayRange = [Carbon::today(), Carbon::today()->endOfDay()];
        $weekRange  = [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
        $monthRange = [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];

        $revenueDay = Cache::remember('revenue_day_' . Carbon::today()->toDateString(), 300, fn() => $this->revenueInRange($todayRange));
        $revenueWeek = Cache::remember('revenue_week_' . Carbon::now()->startOfWeek()->toDateString(), 300, fn() => $this->revenueInRange($weekRange));
        $revenueMonth = Cache::remember('revenue_month_' . Carbon::now()->startOfMonth()->toDateString(), 300, fn() => $this->revenueInRange($monthRange));

        $salesDay = (float) Sale::whereBetween('created_at', $todayRange)->sum('total');
        $salesWeek = (float) Sale::whereBetween('created_at', $weekRange)->sum('total');
        $salesMonth = (float) Sale::whereBetween('created_at', $monthRange)->sum('total');

        $totalDay = $revenueDay + $salesDay;
        $totalWeek = $revenueWeek + $salesWeek;
        $totalMonth = $revenueMonth + $salesMonth;

        $revenueByService = (clone $completed)
            ->join('appointment_service', 'appointments.id', '=', 'appointment_service.appointment_id')
            ->join('services', 'appointment_service.service_id', '=', 'services.id')
            ->select('services.name', DB::raw('SUM(appointment_service.price) as total'))
            ->groupBy('services.name')
            ->orderByDesc('total')
            ->get();

        $completedCount = (clone $completed)->count();
        $pendingCount = (clone $pending)->count();
        $cancelledCount = (clone $cancelled)->count();

        $uniqueCustomers = (clone $completed)->distinct('customer_id')->count('customer_id');

        $avgTicket = $completedCount > 0 ? $revenue / $completedCount : 0;

        $countDay = Appointment::where('status', 'completed')
            ->whereBetween('start', $todayRange)->count();
        $countWeek = Appointment::where('status', 'completed')
            ->whereBetween('start', $weekRange)->count();
        $countMonth = Appointment::where('status', 'completed')
            ->whereBetween('start', $monthRange)->count();

        // --- Gráfico de barras (7 dias) ---
        $chartData = $this->weeklyChartData();

        // --- Top 5 serviços do período ---
        $topServices = (clone $completed)
            ->join('appointment_service', 'appointments.id', '=', 'appointment_service.appointment_id')
            ->join('services', 'appointment_service.service_id', '=', 'services.id')
            ->select('services.id', 'services.name', 'services.color_hex',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(appointment_service.price) as total_revenue'))
            ->groupBy('services.id', 'services.name', 'services.color_hex')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // --- Atendimentos de hoje ---
        $todayAppointments = Appointment::with(['customer', 'services', 'user'])
            ->whereDate('start', Carbon::today())
            ->orderBy('start')
            ->get();

        // --- Próximos 5 agendamentos ---
        $upcomingAppointments = Appointment::with(['customer', 'services', 'user'])
            ->where('start', '>=', Carbon::now())
            ->whereNotIn('status', ['completed', 'cancelled', 'no_show'])
            ->orderBy('start')
            ->limit(5)
            ->get();

        // --- Aniversariantes ---
        $todayBirthdays = Customer::whereMonth('birth_date', Carbon::now()->month)
            ->whereDay('birth_date', Carbon::now()->day)
            ->get();

        $monthBirthdays = Customer::whereMonth('birth_date', Carbon::now()->month)
            ->orderBy('birth_date')
            ->get();

        // --- Comissões pendentes ---
        $pendingCommissions = Commission::where('paid', false)->sum('value');

        // --- Comparativo com período anterior ---
        $periodLength = $periodStart->diffInDays($periodEnd) + 1;
        $prevPeriodStart = $periodStart->copy()->subDays($periodLength);
        $prevPeriodEnd = $periodStart->copy()->subDay();

        $prevRevenue = (float) Appointment::where('status', 'completed')
            ->whereBetween('start', [$prevPeriodStart, $prevPeriodEnd])
            ->join('appointment_service', 'appointments.id', '=', 'appointment_service.appointment_id')
            ->sum('appointment_service.price');

        $prevCompletedCount = Appointment::where('status', 'completed')
            ->whereBetween('start', [$prevPeriodStart, $prevPeriodEnd])
            ->count();

        $revenueChange = $prevRevenue > 0
            ? round(($revenue - $prevRevenue) / $prevRevenue * 100, 1)
            : ($revenue > 0 ? 100 : 0);

        $completedChange = $prevCompletedCount > 0
            ? round(($completedCount - $prevCompletedCount) / $prevCompletedCount * 100, 1)
            : ($completedCount > 0 ? 100 : 0);

        // --- Taxa de cancelamento ---
        $totalFinished = $completedCount + $cancelledCount;
        $cancellationRate = $totalFinished > 0 ? round($cancelledCount / $totalFinished * 100, 1) : 0;

        // --- Taxa de conversão ---
        $totalInPeriod = (clone $baseAppointments)->count();
        $conversionRate = $totalInPeriod > 0 ? round($completedCount / $totalInPeriod * 100, 1) : 0;

        // --- Dia da semana mais movimentado ---
        $dayNames = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
        $dayCounts = (clone $completed)
            ->get()
            ->countBy(fn($app) => $app->start->dayOfWeek);
        $busiestDayIndex = $dayCounts->sortDesc()->keys()->first();
        $busiestDayName = $busiestDayIndex !== null ? $dayNames[$busiestDayIndex] : '—';
        $busiestDayCount = $dayCounts->get($busiestDayIndex, 0);

        // --- Performance por profissional (admin) ---
        $profPerformance = collect();
        if ($isAdmin) {
            $profPerformance = User::where('active', true)
                ->withCount(['appointments' => function ($q) use ($periodStart, $periodEnd) {
                    $q->where('status', 'completed')
                      ->whereBetween('start', [$periodStart, $periodEnd]);
                }])
                ->get()
                ->filter(fn($u) => $u->appointments_count > 0)
                ->values();
        }

        // --- Receita por profissional (admin) ---
        $profRevenue = collect();
        if ($isAdmin) {
            $profRevenue = (clone $completed)
                ->join('appointment_service', 'appointments.id', '=', 'appointment_service.appointment_id')
                ->join('users', 'appointments.user_id', '=', 'users.id')
                ->select('users.id', 'users.name',
                    DB::raw('COUNT(DISTINCT appointments.id) as total_appointments'),
                    DB::raw('SUM(appointment_service.price) as total_revenue'))
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('total_revenue')
                ->get();
        }

        return view('admin.dashboard', compact(
            'revenue', 'salesTotal', 'salesCount', 'completedCount', 'pendingCount', 'cancelledCount',
            'uniqueCustomers', 'avgTicket',
            'todayAppointments', 'upcomingAppointments',
            'todayBirthdays', 'monthBirthdays',
            'period', 'chartData',
            'revenueDay', 'revenueWeek', 'revenueMonth',
            'salesDay', 'salesWeek', 'salesMonth',
            'totalDay', 'totalWeek', 'totalMonth',
            'totalRevenue', 'revenueByService',
            'countDay', 'countWeek', 'countMonth',
            'topServices', 'pendingCommissions',
            'profPerformance', 'isAdmin',
            'revenueChange', 'completedChange',
            'cancellationRate', 'conversionRate', 'totalInPeriod', 'totalFinished',
            'busiestDayName', 'busiestDayCount',
            'profRevenue'
        ));
    }

    private function revenueInRange(array $range): float
    {
        return (float) Appointment::where('status', 'completed')
            ->whereBetween('start', $range)
            ->join('appointment_service', 'appointments.id', '=', 'appointment_service.appointment_id')
            ->sum('appointment_service.price');
    }

    private function weeklyChartData(): array
    {
        $data = [];
        for ($i = 0; $i < 7; $i++) {
            $day = Carbon::now()->startOfWeek()->addDays($i);
            $total = Appointment::where('status', 'completed')
                ->whereDate('start', $day)
                ->join('appointment_service', 'appointments.id', '=', 'appointment_service.appointment_id')
                ->sum('appointment_service.price');
            $data[] = [
                'label' => $day->locale('pt-BR')->isoFormat('ddd'),
                'value' => (float) $total,
            ];
        }
        return $data;
    }
}
