<?php
chdir(__DIR__);
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    
    // Truncate tables that depend on services
    DB::table('appointment_service')->truncate();
    DB::table('services')->truncate();
    
    DB::statement('SET FOREIGN_KEY_CHECKS=1');

    $services = [
        ['id' => 1, 'name' => 'BARBA',               'duration_min' => 30,  'price' => 35.00, 'color_hex' => '#243B53', 'description' => 'Barba completa com navalha'],
        ['id' => 2, 'name' => 'BARBA EXPRESS',        'duration_min' => 30,  'price' => 25.00, 'color_hex' => '#334155', 'description' => 'Barba rápida com máquina'],
        ['id' => 3, 'name' => 'COMBO CORTE + BARBA',  'duration_min' => 60,  'price' => 70.00, 'color_hex' => '#1E293B', 'description' => 'Combo completo corte masculino + barba'],
        ['id' => 4, 'name' => 'CORTE',                'duration_min' => 30,  'price' => 45.00, 'color_hex' => '#475569', 'description' => 'Corte masculino com acabamento'],
        ['id' => 5, 'name' => 'CORTE + BARBA + TINTURA', 'duration_min' => 90,'price' => 100.00,'color_hex' => '#0F172A', 'description' => 'Combo corte + barba + tintura'],
        ['id' => 6, 'name' => 'CORTE ALINHAMENTO AMERICANO','duration_min' => 30,'price' => 50.00,'color_hex' => '#64748B', 'description' => 'Corte com alinhamento americano'],
        ['id' => 7, 'name' => 'CORTE CONTORNO PEZINHO','duration_min' => 30,'price' => 40.00,'color_hex' => '#94A3B8', 'description' => 'Corte com contorno e pezinho'],
        ['id' => 8, 'name' => 'DEPILAÇÃO NARIZ',      'duration_min' => 30,  'price' => 15.00, 'color_hex' => '#CBD5E1', 'description' => 'Depilação de nariz'],
        ['id' => 9, 'name' => 'DEPILAÇÃO ORELHAS',    'duration_min' => 30,  'price' => 15.00, 'color_hex' => '#E2E8F0', 'description' => 'Depilação de orelhas'],
        ['id' => 10,'name' => 'RASPAGEM NA SHAVER',   'duration_min' => 30,  'price' => 30.00, 'color_hex' => '#334155', 'description' => 'Raspagem com shaver elétrico'],
        ['id' => 11,'name' => 'RASPAGEM SÓ NA MÁQUINA','duration_min' => 30, 'price' => 25.00, 'color_hex' => '#475569', 'description' => 'Raspagem apenas com máquina'],
        ['id' => 12,'name' => 'SOBRANCELHA',          'duration_min' => 30,  'price' => 20.00, 'color_hex' => '#64748B', 'description' => 'Design e alinhamento de sobrancelha'],
    ];

    foreach ($services as $s) {
        DB::table('services')->insert([
            'id'            => $s['id'],
            'name'          => $s['name'],
            'duration_min'  => $s['duration_min'],
            'price'         => $s['price'],
            'color_hex'     => $s['color_hex'],
            'description'   => $s['description'],
            'active'        => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    echo "<p style='color:green'>✓ " . count($services) . " serviços importados com sucesso!</p>";
    echo "<ul>";
    foreach ($services as $s) {
        echo "<li><b>{$s['name']}</b> — R$ " . number_format($s['price'], 2, ',', '.') . " — {$s['duration_min']}min</li>";
    }
    echo "</ul>";

    // Also create attendants if they don't exist
    $attendants = [
        ['name' => 'Atendente',   'email' => 'atendente@agenda.com',      'password' => bcrypt('123456'), 'role' => 'attendant'],
        ['name' => 'Atendente 2', 'email' => 'atendente2@barbearia.com',  'password' => bcrypt('123456'), 'role' => 'attendant'],
    ];

    foreach ($attendants as $a) {
        $existing = DB::table('users')->where('email', $a['email'])->first();
        if (!$existing) {
            DB::table('users')->insert([
                'name'     => $a['name'],
                'email'    => $a['email'],
                'password' => $a['password'],
                'role'     => $a['role'],
                'active'   => true,
            ]);
            echo "<p style='color:green'>✓ Atendente {$a['name']} criado</p>";
        } else {
            echo "<p style='color:orange'>⚠ Atendente {$a['name']} já existe</p>";
        }
    }

} catch (\Exception $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}

echo "<p style='color:red'>⚠ DELETE _seed_services.php APÓS USAR</p>";
echo "<p><a href='/admin/dashboard'>Ir para o painel</a></p>";
