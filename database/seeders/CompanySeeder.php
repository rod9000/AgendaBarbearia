<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run()
    {
        Company::updateOrCreate(
            ['slug' => 'barbearia-andre'],
            [
                'name' => 'Barbearia Andrê',
                'email' => 'contato@barbeariaandre.com',
                'phone' => '(44) 99713-5071',
                'whatsapp' => '44997135071',
                'trial_starts_at' => now(),
                'trial_ends_at' => now()->addMonths(2),
                'active' => true,
            ]
        );
    }
}
