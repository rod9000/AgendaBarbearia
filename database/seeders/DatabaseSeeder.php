<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(CompanySeeder::class);

        $company = Company::first();

        User::create([
            'name' => 'Administrador',
            'email' => 'admin@agenda.com',
            'password' => bcrypt('123456'),
            'phone' => '(11) 99999-8888',
            'role' => 'admin',
            'active' => true,
            'company_id' => $company->id,
        ]);

        User::create([
            'name' => 'Atendente',
            'email' => 'atendente@agenda.com',
            'password' => bcrypt('123456'),
            'phone' => '(11) 97777-6666',
            'role' => 'attendant',
            'active' => true,
            'company_id' => $company->id,
        ]);

        $this->call(ServiceSeeder::class);
    }
}
