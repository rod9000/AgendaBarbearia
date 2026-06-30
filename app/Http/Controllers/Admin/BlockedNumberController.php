<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockedNumberController extends Controller
{
    public function index()
    {
        $company = Auth::user()->company;
        $blockedNumbers = BlockedNumber::where('company_id', $company->id)
            ->latest()
            ->paginate(20);

        return view('admin.settings.blocked-numbers', compact('blockedNumbers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|string|max:20',
            'name' => 'nullable|string|max:100',
            'reason' => 'nullable|string|max:500',
        ]);

        $company = Auth::user()->company;
        $phone = preg_replace('/\D/', '', $data['phone']);

        $exists = BlockedNumber::where('company_id', $company->id)
            ->where('phone', $phone)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Este número já está bloqueado.');
        }

        BlockedNumber::create([
            'company_id' => $company->id,
            'phone' => $phone,
            'name' => $data['name'],
            'reason' => $data['reason'],
        ]);

        return back()->with('success', 'Número bloqueado com sucesso!');
    }

    public function destroy(BlockedNumber $blockedNumber)
    {
        $blockedNumber->delete();
        return back()->with('success', 'Número desbloqueado!');
    }
}
