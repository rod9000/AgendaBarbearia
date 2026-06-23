<?php
chdir(__DIR__);
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Artisan;

echo "<pre>";
Artisan::call('migrate', ['--force' => true]);
echo Artisan::output();
echo "\n<hr><p style='color:red'>⚠ DELETE _migrate.php APÓS USAR</p>";
echo "<p><a href='/admin/dashboard'>Ir para o painel</a></p>";
