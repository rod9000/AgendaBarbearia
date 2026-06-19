<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Console\Command;

class BackfillAppointmentServices extends Command
{
    protected $signature = 'appointments:backfill-services';
    protected $description = 'Popula a tabela pivot appointment_service para agendamentos existentes';

    public function handle()
    {
        $appointments = Appointment::whereDoesntHave('services')->get();
        $bar = $this->output->createProgressBar($appointments->count());
        $bar->start();

        foreach ($appointments as $app) {
            if ($app->service_id && $app->service) {
                $service = $app->service;
                $app->services()->sync([
                    $service->id => [
                        'price'        => $service->price,
                        'duration_min' => $service->duration_min,
                    ],
                ]);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Pivot appointment_service populado para ' . $appointments->count() . ' agendamentos.');
    }
}
