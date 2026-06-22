<?php
chdir(__DIR__);
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $user = \App\Models\User::where('email', 'admin@agenda.com')->first();
    if (!$user) {
        $user = \App\Models\User::create([
            'name' => 'Admin',
            'email' => 'admin@agenda.com',
            'password' => bcrypt('123456'),
            'role' => 'admin',
            'active' => true,
        ]);
        echo "<p style='color:green'>✓ Usuário admin criado com sucesso!</p>";
    } else {
        echo "<p style='color:orange'>⚠ Usuário admin@agenda.com já existe.</p>";
    }
    echo "<p>Email: <b>admin@agenda.com</b></p>";
    echo "<p>Senha: <b>123456</b></p>";
    echo "<p><a href='/admin/dashboard'>Ir para o painel</a></p>";
} catch (\Exception $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}
echo "<p style='color:red'>⚠ DELETE _create_admin.php APÓS USAR</p>";
