<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Update existing services
        DB::table('services')->where('name', 'Drenagem Gestante')->update([
            'name'  => 'Massagem Gestante',
            'price' => 130.00,
        ]);

        DB::table('services')->where('name', 'Massagem Express')->delete();

        DB::table('services')->where('name', 'Massagem Relaxante')->update([
            'price' => 130.00,
        ]);

        DB::table('services')->where('name', 'Detox / Argiloterapia')->update([
            'name'  => 'Detox Corporal',
            'price' => 160.00,
        ]);

        DB::table('services')->where('name', 'Drenagem Facial')->update([
            'price' => 70.00,
        ]);

        DB::table('services')->where('name', 'Hidratação dos Lábios')->update([
            'name'  => 'Hidratação Labial',
            'price' => 50.00,
        ]);

        DB::table('services')->where('name', 'Revitalização Facial')->update([
            'price' => 120.00,
        ]);

        DB::table('services')->where('name', 'SPA dos Pés')->update([
            'name'  => 'Spa dos Pés',
            'price' => 100.00,
        ]);

        // Add new services
        $newServices = [
            ['name' => 'Pedras Quentes',       'duration_min' => 60, 'price' => 140.00, 'color_hex' => '#ef4444', 'description' => 'Massagem com pedras basálticas aquecidas que proporcionam relaxamento profundo e alívio de tensões.', 'active' => true],
            ['name' => 'Ventosaterapia',       'duration_min' => 40, 'price' => 140.00, 'color_hex' => '#a855f7', 'description' => 'Técnica com ventosas que estimula a circulação sanguínea, alivia dores musculares e promove sensação de bem-estar.', 'active' => true],
            ['name' => 'Bambuterapia',         'duration_min' => 60, 'price' => 140.00, 'color_hex' => '#65a30d', 'description' => 'Massagem com bambus de diferentes diâmetros que modela o corpo, reduz medidas e alivia tensões.', 'active' => true],
            ['name' => 'Vela Quente',           'duration_min' => 50, 'price' => 140.00, 'color_hex' => '#f59e0b', 'description' => 'Massagem com óleo de vela aquecido rico em vitaminas que hidrata profundamente a pele e relaxa o corpo.', 'active' => true],
            ['name' => 'Massagem Desportiva',   'duration_min' => 60, 'price' => 150.00, 'color_hex' => '#3b82f6', 'description' => 'Massagem voltada para atletas e praticantes de atividade física que auxilia na recuperação muscular e prevenção de lesões.', 'active' => true],
            ['name' => 'Drenagem Linfática',    'duration_min' => 60, 'price' => 130.00, 'color_hex' => '#ec4899', 'description' => 'Técnica com movimentos suaves que pode ajudar a reduzir o inchaço, aliviar a retenção de líquidos e promover sensação de leveza no corpo.', 'active' => true],
            ['name' => 'Limpeza de Pele',       'duration_min' => 60, 'price' => 150.00, 'color_hex' => '#3b82f6', 'description' => 'Higienização profunda com extração de cravos e aplicação de ativos para purificar e renovar a pele.', 'active' => true],
        ];

        $existingNames = DB::table('services')->pluck('name')->toArray();

        foreach ($newServices as $svc) {
            if (!in_array($svc['name'], $existingNames)) {
                DB::table('services')->insert($svc);
            }
        }
    }

    public function down()
    {
        // Not reversible
    }
};
