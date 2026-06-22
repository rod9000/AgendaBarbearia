<?php
// Migration trigger - DELETE AFTER USE
chdir(__DIR__);
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->call('migrate:fresh', ['--force' => true, '--drop-views' => true, '--seed' => false]);
echo "<pre>" . $kernel->output() . "</pre>";
echo "<p style='color:red'>⚠ DELETE THIS FILE NOW</p>";
