<?php
define('LARAVEL_START', microtime(true));

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$status = $kernel->handle(
    $input = new Symfony\Component\Console\Input\ArrayInput(['command' => 'migrate', '--force' => true]),
    $output = new Symfony\Component\Console\Output\BufferedOutput()
);

echo '<pre>';
echo "=== Migrações ===\n";
echo $output->fetch();
echo "\n";

$status2 = $kernel->handle(
    $input = new Symfony\Component\Console\Input\ArrayInput(['command' => 'cache:clear']),
    $output2 = new Symfony\Component\Console\Output\BufferedOutput()
);
echo "=== Cache Clear ===\n";
echo $output2->fetch();
echo "\n";

$status3 = $kernel->handle(
    $input = new Symfony\Component\Console\Input\ArrayInput(['command' => 'view:clear']),
    $output3 = new Symfony\Component\Console\Output\BufferedOutput()
);
echo "=== View Clear ===\n";
echo $output3->fetch();
echo "\n";

echo "✅ Migrações executadas com sucesso!";
echo '</pre>';
