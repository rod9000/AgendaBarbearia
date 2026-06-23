<?php
chdir(__DIR__);
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$company = DB::table('companies')->where('active', true)->first();

if ($company) {
    DB::table('companies')->where('id', $company->id)->update(['whatsapp' => '(44) 99713-5071']);
    echo "<p style='color:green'>✓ WhatsApp atualizado para (44) 99713-5071 na empresa <b>{$company->name}</b></p>";
} else {
    echo "<p style='color:red'>Nenhuma empresa ativa encontrada.</p>";
}

echo "<p style='color:red'>⚠ DELETE _seed_whatsapp.php APÓS USAR</p>";
echo "<p><a href='/agendar'>Ir para o agendamento</a></p>";
