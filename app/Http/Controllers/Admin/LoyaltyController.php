<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LoyaltyReward;
use App\Models\LoyaltyRedemption;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function index()
    {
        $rewards = LoyaltyReward::orderBy('points_required')->get();

        $topCustomers = Customer::where('points', '>', 0)
            ->orderBy('points', 'desc')
            ->take(10)
            ->get(['id', 'name', 'points', 'total_visits']);

        $totalPointsGiven = Customer::sum('points');
        $totalRedemptions = LoyaltyRedemption::count();
        $totalCustomersWithPoints = Customer::where('points', '>', 0)->count();

        return view('admin.loyalty.index', compact(
            'rewards', 'topCustomers', 'totalPointsGiven',
            'totalRedemptions', 'totalCustomersWithPoints'
        ));
    }

    public function create()
    {
        return view('admin.loyalty.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'description'      => 'nullable|string',
            'points_required'  => 'required|integer|min:1',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'active'           => 'nullable|boolean',
        ]);

        $data['active'] = $request->boolean('active');

        LoyaltyReward::create($data);

        return redirect()->route('admin.loyalty.index')
            ->with('success', 'Recompensa cadastrada com sucesso!');
    }

    public function edit(LoyaltyReward $loyaltyReward)
    {
        return view('admin.loyalty.edit', compact('loyaltyReward'));
    }

    public function update(Request $request, LoyaltyReward $loyaltyReward)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'description'      => 'nullable|string',
            'points_required'  => 'required|integer|min:1',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'active'           => 'nullable|boolean',
        ]);

        $data['active'] = $request->boolean('active');

        $loyaltyReward->update($data);

        return redirect()->route('admin.loyalty.index')
            ->with('success', 'Recompensa atualizada com sucesso!');
    }

    public function destroy(LoyaltyReward $loyaltyReward)
    {
        $loyaltyReward->redemptions()->delete();
        $loyaltyReward->delete();

        return redirect()->route('admin.loyalty.index')
            ->with('success', 'Recompensa excluída com sucesso!');
    }

    public function customerPoints(Customer $customer)
    {
        $customer->load('redemptions.reward');
        $rewards = LoyaltyReward::where('active', true)->orderBy('points_required')->get();

        return view('admin.loyalty.customer', compact('customer', 'rewards'));
    }

    public function redeem(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'reward_id' => 'required|exists:loyalty_rewards,id',
        ]);

        $reward = LoyaltyReward::findOrFail($data['reward_id']);

        if (!$reward->active) {
            return redirect()->back()->with('error', 'Esta recompensa não está mais disponível.');
        }

        if ($customer->points < $reward->points_required) {
            return redirect()->back()->with('error', 'Cliente não tem pontos suficientes.');
        }

        $customer->spendPoints($reward->points_required);

        LoyaltyRedemption::create([
            'customer_id'      => $customer->id,
            'loyalty_reward_id' => $reward->id,
            'points_spent'     => $reward->points_required,
        ]);

        return redirect()->back()->with('success', "Recompensa '{$reward->name}' resgatada com sucesso!");
    }
}
