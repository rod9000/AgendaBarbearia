<?php

namespace Database\Seeders;

use App\Models\BotMenuItem;
use App\Models\Company;
use Illuminate\Database\Seeder;

class BotMenuSeeder extends Seeder
{
    public function run()
    {
        $company = Company::first();

        if (!$company) {
            return;
        }

        $items = [
            [
                'menu_number' => 1,
                'label' => 'Agendar Horário',
                'action' => 'booking',
                'response_text' => null,
                'sort_order' => 1,
            ],
            [
                'menu_number' => 2,
                'label' => 'Serviços e Preços',
                'action' => 'services',
                'response_text' => null,
                'sort_order' => 2,
            ],
            [
                'menu_number' => 3,
                'label' => 'Horários de Funcionamento',
                'action' => 'working_hours',
                'response_text' => null,
                'sort_order' => 3,
            ],
            [
                'menu_number' => 4,
                'label' => 'Consultar Agendamentos',
                'action' => 'consult',
                'response_text' => null,
                'sort_order' => 4,
            ],
            [
                'menu_number' => 5,
                'label' => 'Cancelar Agendamento',
                'action' => 'cancel',
                'response_text' => null,
                'sort_order' => 5,
            ],
            [
                'menu_number' => 6,
                'label' => 'Localização',
                'action' => 'location',
                'response_text' => null,
                'sort_order' => 6,
            ],
        ];

        foreach ($items as $item) {
            BotMenuItem::updateOrCreate(
                ['company_id' => $company->id, 'menu_number' => $item['menu_number']],
                $item
            );
        }

        $this->command->info('Menu do bot criado com ' . count($items) . ' itens.');
    }
}
