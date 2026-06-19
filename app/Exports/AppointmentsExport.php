<?php

namespace App\Exports;

use App\Models\Appointment;

class AppointmentsExport
{
    public function exportCsv($start = null, $end = null, $userId = null, $status = null)
    {
        $query = Appointment::with(['customer', 'services', 'user']);

        if ($start && $end) {
            $query->whereBetween('start', [$start, $end]);
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $appointments = $query->orderBy('start')->get();

        $statusLabels = [
            'scheduled' => 'Agendado', 'confirmed' => 'Confirmado',
            'in_progress' => 'Em Andamento', 'completed' => 'Concluído',
            'cancelled' => 'Cancelado', 'no_show' => 'Não Compareceu',
        ];

        $csv = "Data;Hora Início;Hora Fim;Cliente;CPF;Telefone;Serviço(s);Profissional;Status;Valor\n";

        foreach ($appointments as $app) {
            $serviceNames = $app->services->pluck('name')->implode(' + ');
            $totalPrice = $app->services->sum('pivot.price');

            $csv .= implode(';', [
                $app->start->format('d/m/Y'),
                $app->start->format('H:i'),
                $app->end->format('H:i'),
                $app->customer->name,
                $app->customer->cpf,
                $app->customer->phone,
                $serviceNames,
                $app->user->name,
                $statusLabels[$app->status] ?? $app->status,
                number_format($totalPrice, 2, ',', '.'),
            ]) . "\n";
        }

        return $csv;
    }
}
