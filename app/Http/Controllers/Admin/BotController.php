<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BotController extends Controller
{
    public function index()
    {
        $company = Auth::user()->company;

        return view('admin.settings.bot', compact('company'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bot_enabled' => 'nullable',
            'welcome_message' => 'nullable|string|max:1000',
            'off_hours_message' => 'nullable|string|max:1000',
            'bot_response_delay_minutes' => 'required|integer|min:0|max:1440',
            'bot_off_hours_enabled' => 'nullable',
        ]);

        $company = Auth::user()->company;
        $company->update([
            'bot_enabled' => isset($data['bot_enabled']),
            'welcome_message' => $data['welcome_message'] ?: null,
            'off_hours_message' => $data['off_hours_message'] ?: null,
            'bot_response_delay_minutes' => (int) $data['bot_response_delay_minutes'],
            'bot_off_hours_enabled' => isset($data['bot_off_hours_enabled']),
        ]);

        return redirect()->route('admin.settings.bot')
            ->with('success', 'Configurações do bot salvas!');
    }
}
