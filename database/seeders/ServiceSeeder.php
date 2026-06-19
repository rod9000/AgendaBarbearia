<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        $services = config('services_default');

        foreach ($services as $s) {
            Service::create($s);
        }
    }
}
