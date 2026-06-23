<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedSlot;
use App\Models\Company;
use App\Models\User;
use App\Models\WorkingHour;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function workingHours()
    {
        $users = User::where('active', true)->orderBy('name')->get();
        $days = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];

        $hours = [];
        foreach ($users as $user) {
            foreach (range(0, 6) as $day) {
                $hours[$user->id][$day] = WorkingHour::where('user_id', $user->id)
                    ->where('day_of_week', $day)
                    ->orderBy('start_time')
                    ->get();
            }
        }

        return view('admin.settings.working-hours', compact('users', 'days', 'hours'));
    }

    public function workingHoursStore(Request $request)
    {
        $data = $request->validate([
            'user_id'           => 'required|exists:users,id',
            'day_of_week'       => 'required|integer|between:0,6',
            'blocks'            => 'required|array|min:1',
            'blocks.*.start'    => 'required|date_format:H:i',
            'blocks.*.end'      => 'required|date_format:H:i|after:blocks.*.start',
        ]);

        WorkingHour::where('user_id', $data['user_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->delete();

        foreach ($data['blocks'] as $block) {
            WorkingHour::create([
                'user_id'     => $data['user_id'],
                'day_of_week' => $data['day_of_week'],
                'start_time'  => $block['start'],
                'end_time'    => $block['end'],
                'active'      => true,
            ]);
        }

        return redirect()->back()->with('success', 'Horários atualizados com sucesso!');
    }

    public function workingHoursCopy(Request $request)
    {
        $data = $request->validate([
            'user_id'      => 'required|exists:users,id',
            'source_day'   => 'required|integer|between:0,6',
            'target_days'  => 'required|array|min:1',
            'target_days.*' => 'integer|between:0,6',
        ]);

        $blocks = WorkingHour::where('user_id', $data['user_id'])
            ->where('day_of_week', $data['source_day'])
            ->orderBy('start_time')
            ->get();

        if ($blocks->isEmpty()) {
            return redirect()->back()->with('error', 'O dia de origem não possui horários definidos.');
        }

        foreach ($data['target_days'] as $targetDay) {
            WorkingHour::where('user_id', $data['user_id'])
                ->where('day_of_week', $targetDay)
                ->delete();

            foreach ($blocks as $block) {
                WorkingHour::create([
                    'user_id'     => $data['user_id'],
                    'day_of_week' => $targetDay,
                    'start_time'  => $block->start_time,
                    'end_time'    => $block->end_time,
                    'active'      => true,
                ]);
            }
        }

        $dayNames = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
        $targetLabels = array_map(fn($d) => $dayNames[$d], $data['target_days']);
        return redirect()->back()->with('success', 'Horários copiados de ' . $dayNames[$data['source_day']] . ' para: ' . implode(', ', $targetLabels) . '.');
    }

    public function blockedSlots()
    {
        $users = User::where('active', true)->orderBy('name')->get();
        $slots = BlockedSlot::with('user')->where('start', '>=', Carbon::now())->orderBy('start')->paginate(20);

        return view('admin.settings.blocked-slots', compact('users', 'slots'));
    }

    public function blockedSlotsStore(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'start'   => 'required|date',
            'end'     => 'required|date|after:start',
            'reason'  => 'nullable|string|max:255',
        ]);

        BlockedSlot::create($data);

        return redirect()->back()->with('success', 'Bloqueio cadastrado com sucesso!');
    }

    public function blockedSlotsDestroy(BlockedSlot $blockedSlot)
    {
        $blockedSlot->delete();
        return redirect()->back()->with('success', 'Bloqueio removido!');
    }

    public function whatsapp()
    {
        $company = Company::where('active', true)->first();
        return view('admin.settings.whatsapp', compact('company'));
    }

    public function whatsappStore(Request $request)
    {
        $data = $request->validate([
            'whatsapp' => 'nullable|string|max:20',
        ]);

        $company = Company::where('active', true)->first();
        if ($company) {
            $company->update(['whatsapp' => $data['whatsapp']]);
            return redirect()->route('admin.settings.whatsapp')
                ->with('success', 'WhatsApp atualizado com sucesso!');
        }

        return redirect()->back()->with('error', 'Nenhuma empresa encontrada.');
    }
}
