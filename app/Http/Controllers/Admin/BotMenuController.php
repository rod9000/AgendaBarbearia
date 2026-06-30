<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BotMenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BotMenuController extends Controller
{
    public function index()
    {
        $company = Auth::user()->company;
        $menuItems = BotMenuItem::where('company_id', $company->id)
            ->orderBy('sort_order')
            ->get();
        $actionTypes = BotMenuItem::getActionTypes();

        return view('admin.settings.bot-menu', compact('company', 'menuItems', 'actionTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => 'required|string|max:100',
            'action' => 'required|in:booking,services,working_hours,consult,cancel,location,custom',
            'response_text' => 'nullable|string|max:1000',
        ]);

        $company = Auth::user()->company;

        $existingNumbers = BotMenuItem::where('company_id', $company->id)
            ->pluck('menu_number')
            ->toArray();

        $nextNumber = 1;
        while (in_array($nextNumber, $existingNumbers)) {
            $nextNumber++;
        }

        BotMenuItem::create([
            'company_id' => $company->id,
            'menu_number' => $nextNumber,
            'label' => $data['label'],
            'action' => $data['action'],
            'response_text' => $data['response_text'],
            'sort_order' => $nextNumber,
        ]);

        return back()->with('success', "Item do menu adicionado como opção {$nextNumber}!");
    }

    public function update(Request $request, BotMenuItem $menuItem)
    {
        $data = $request->validate([
            'label' => 'required|string|max:100',
            'action' => 'required|in:booking,services,working_hours,consult,cancel,location,custom',
            'response_text' => 'nullable|string|max:1000',
            'is_active' => 'nullable',
        ]);

        $menuItem->update([
            'label' => $data['label'],
            'action' => $data['action'],
            'response_text' => $data['response_text'],
            'is_active' => isset($data['is_active']),
        ]);

        return back()->with('success', 'Item atualizado!');
    }

    public function destroy(BotMenuItem $menuItem)
    {
        $company = Auth::user()->company;
        $menuItem->delete();

        $remaining = BotMenuItem::where('company_id', $company->id)
            ->orderBy('sort_order')
            ->get();

        foreach ($remaining as $index => $item) {
            $item->update([
                'menu_number' => $index + 1,
                'sort_order' => $index + 1,
            ]);
        }

        return back()->with('success', 'Item removido!');
    }

    public function reorder(Request $request)
    {
        $company = Auth::user()->company;
        $order = $request->input('order', []);

        foreach ($order as $position => $id) {
            BotMenuItem::where('company_id', $company->id)
                ->where('id', $id)
                ->update(['sort_order' => $position]);
        }

        return response()->json(['success' => true]);
    }
}
