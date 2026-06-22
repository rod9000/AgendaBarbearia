<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run()
    {
        Company::create([
            'name' => 'Barbearia Andrê',
            'slug' => 'barbearia-andre',
            'email' => 'contato@barbeariaandre.com',
            'phone' => '(44) 99713-5071',
            'trial_starts_at' => now(),
            'trial_ends_at' => now()->addDays(30),
            'active' => true,
        ]);
    }
}
