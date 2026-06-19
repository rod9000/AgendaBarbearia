<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        $userId = $request->get('user_id');

        $dateRange = match ($period) {
            'week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            default => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        };

        $query = Commission::with(['user', 'appointment.customer', 'appointment.service'])
            ->whereBetween('created_at', $dateRange);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $commissions = $query->latest()->paginate(20);

        $totalCommissions = $query->clone()->sum('value');
        $totalPaid = $query->clone()->where('paid', true)->sum('value');
        $totalPending = $query->clone()->where('paid', false)->sum('value');

        $users = User::where('active', true)->orderBy('name')->get();

        $byUser = Commission::whereBetween('created_at', $dateRange)
            ->selectRaw('user_id, SUM(value) as total, COUNT(*) as count, SUM(CASE WHEN paid = 1 THEN value ELSE 0 END) as paid_total')
            ->groupBy('user_id')
            ->get();

        return view('admin.commissions.index', compact(
            'commissions', 'totalCommissions', 'totalPaid', 'totalPending',
            'users', 'byUser', 'period', 'userId'
        ));
    }

    public function professionalStatement(Request $request, User $user)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $commissions = Commission::with(['appointment.customer', 'appointment.services'])
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->paginate(30);

        $total = $commissions->sum('value');
        $totalPaid = $commissions->where('paid', true)->sum('value');
        $totalPending = $commissions->where('paid', false)->sum('value');

        $months = collect(range(1, 12))->mapWithKeys(fn($m) => [$m => Carbon::create()->month($m)->format('F')]);
        $years = range(now()->year - 2, now()->year);

        return view('admin.commissions.professional', compact(
            'user', 'commissions', 'total', 'totalPaid', 'totalPending',
            'month', 'year', 'months', 'years'
        ));
    }

    public function markPaid(Commission $commission)
    {
        $commission->update([
            'paid' => true,
            'paid_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Comissão marcada como paga!');
    }
}
