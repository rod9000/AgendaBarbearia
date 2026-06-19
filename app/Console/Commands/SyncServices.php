<?php

namespace App\Console\Commands;

use App\Models\Service;
use Illuminate\Console\Command;

class SyncServices extends Command
{
    protected $signature = 'services:sync {--force : Sincronizar mesmo em produção sem confirmação}';
    protected $description = 'Sincroniza os procedimentos do config/services_default.php com o banco';

    public function handle()
    {
        if (app()->environment('production') && !$this->option('force')) {
            if (!$this->confirm('Você está em PRODUÇÃO. Deseja continuar?')) {
                $this->info('Sincronização cancelada.');
                return;
            }
        }

        $defaults = config('services_default');
        $created = 0;
        $updated = 0;

        foreach ($defaults as $data) {
            $service = Service::where('name', $data['name'])->first();

            if ($service) {
                $service->update($data);
                $updated++;
                $this->line("Atualizado: {$data['name']}");
            } else {
                Service::create($data);
                $created++;
                $this->line("Criado: {$data['name']}");
            }
        }

        // Desativar serviços que não estão mais na lista
        $names = array_column($defaults, 'name');
        $deactivated = Service::whereNotIn('name', $names)->where('active', true)->update(['active' => false]);
        if ($deactivated) {
            $this->warn("$deactivated serviço(s) desativado(s) por não estarem mais na lista.");
        }

        $this->info("Sincronização concluída! $created criado(s), $updated atualizado(s).");
    }
}
