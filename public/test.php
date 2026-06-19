<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "STEP1: PHP OK\n";

$vendorFile = __DIR__ . '/../vendor/autoload.php';
echo "STEP2: vendor at $vendorFile - " . (file_exists($vendorFile) ? 'EXISTS' : 'NOT FOUND') . "\n";

$envFile = __DIR__ . '/../.env';
echo "STEP3: .env at $envFile - " . (file_exists($envFile) ? 'EXISTS' : 'NOT FOUND') . "\n";

$storageDir = __DIR__ . '/../storage';
echo "STEP4: storage $storageDir - " . (is_dir($storageDir) ? 'EXISTS' : 'NOT FOUND') . "\n";
if (is_dir($storageDir)) {
    echo "  writable: " . (is_writable($storageDir) ? 'YES' : 'NO') . "\n";
}

require $vendorFile;
echo "STEP5: autoload OK\n";

$app = require_once __DIR__ . '/../bootstrap/app.php';
echo "STEP6: bootstrap OK\n";

echo "ALL OK\n";
