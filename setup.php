<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

chdir($_SERVER['DOCUMENT_ROOT']);
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Rodar todas as migrações
$kernel->call('migrate', ['--force' => true]);
echo "Migracoes executadas!<br>";

// Verificar empresa
$company = \App\Models\Company::first();
if ($company) {
    echo "Empresa encontrada: {$company->name}<br>";
} else {
    echo "Empresa nao encontrada<br>";
}

// Vincular empresa ao admin
$admin = \App\Models\User::where('role', 'admin')->first();
if ($admin && $admin->company_id) {
    echo "Admin vinculado: company_id = {$admin->company_id}<br>";
} else {
    echo "Admin sem empresa vinculada<br>";
}

// Limpar cache
$kernel->call('cache:clear');
$kernel->call('view:clear');
echo "Cache limpo!<br>";

echo "<h1 style='color:green'>Setup completo!</h1>";
echo "<p>Delete este arquivo agora.</p>";
