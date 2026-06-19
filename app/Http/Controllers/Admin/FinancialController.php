<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FinancialController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        $method = $request->get('method', '');

        $dateRange = match ($period) {
            'today' => [Carbon::today(), Carbon::today()->endOfDay()],
            'week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            default => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        };

        $query = Payment::with(['appointment.customer', 'appointment.services', 'registeredBy'])
            ->whereBetween('paid_at', $dateRange);

        if ($method) {
            $query->where('method', $method);
        }

        $payments = $query->latest('paid_at')->paginate(20);

        $totalReceipts = Payment::whereBetween('paid_at', $dateRange)->sum('amount');

        $totalPending = Appointment::where('status', 'completed')
            ->whereDoesntHave('payment')
            ->join('appointment_service', 'appointments.id', '=', 'appointment_service.appointment_id')
            ->sum('appointment_service.price');

        $productCost = Appointment::whereIn('status', ['completed', 'in_progress'])
            ->whereBetween('start', $dateRange)
            ->with('services.products')
            ->get()
            ->sum(function ($app) {
                $cost = 0;
                $deductedPerSession = [];
                foreach ($app->services as $service) {
                    foreach ($service->products as $product) {
                        if ($product->pivot->is_per_session) {
                            $key = $product->id . '_' . $app->customer_id . '_' . $app->start->format('Y-m-d');
                            if (isset($deductedPerSession[$key])) continue;
                            $deductedPerSession[$key] = true;
                        }
                        $cost += ($product->purchase_price * $product->pivot->quantity);
                    }
                }
                return $cost;
            });

        $byMethod = Payment::whereBetween('paid_at', $dateRange)
            ->selectRaw('method, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('method')
            ->get();

        $chartData = [];
        if ($period === 'month') {
            $daysInMonth = Carbon::now()->daysInMonth;
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $day = Carbon::now()->startOfMonth()->addDays($i - 1);
                $dayTotal = Payment::whereDate('paid_at', $day)->sum('amount');
                $chartData[] = [
                    'label' => $day->format('d/m'),
                    'value' => (float) $dayTotal,
                ];
            }
        } elseif ($period === 'year') {
            for ($i = 1; $i <= 12; $i++) {
                $monthTotal = Payment::whereYear('paid_at', Carbon::now()->year)
                    ->whereMonth('paid_at', $i)
                    ->sum('amount');
                $chartData[] = [
                    'label' => Carbon::create()->month($i)->locale('pt-BR')->isoFormat('MMM'),
                    'value' => (float) $monthTotal,
                ];
            }
        } elseif ($period === 'week') {
            for ($i = 0; $i < 7; $i++) {
                $day = Carbon::now()->startOfWeek()->addDays($i);
                $dayTotal = Payment::whereDate('paid_at', $day)->sum('amount');
                $chartData[] = [
                    'label' => $day->locale('pt-BR')->isoFormat('ddd'),
                    'value' => (float) $dayTotal,
                ];
            }
        } else {
            $today = Payment::whereDate('paid_at', Carbon::today())->sum('amount');
            $chartData[] = ['label' => 'Hoje', 'value' => (float) $today];
        }

        $profit = $totalReceipts - $productCost;

        return view('admin.financial.index', compact(
            'payments', 'totalReceipts', 'totalPending', 'byMethod', 'chartData', 'period', 'method', 'productCost', 'profit'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'amount' => 'required|numeric|min:0',
            'method' => 'required|in:dinheiro,cartao,pix',
            'paid_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $data['registered_by'] = auth()->id();
        if (!isset($data['paid_at'])) {
            $data['paid_at'] = now();
        }

        $payment = Payment::create($data);

        return response()->json(['success' => true, 'payment' => $payment->load('appointment.customer')]);
    }
}
