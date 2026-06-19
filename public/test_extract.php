<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
@set_time_limit(300);

$vendorPath = __DIR__ . '/../vendor';
$zipFile = __DIR__ . '/../vendor_prod.zip';

echo "checking zip...\n";
if (!file_exists($zipFile)) { die("zip not found"); }
echo "zip exists: " . filesize($zipFile) . " bytes\n";

$zip = new ZipArchive();
$res = $zip->open($zipFile);
if ($res !== TRUE) { die("Failed to open zip. Code: $res"); }

echo "zip opened OK, " . $zip->numFiles . " files\n";
echo "extracting...\n";

$result = $zip->extractTo(__DIR__ . '/..');
echo "extractTo returned: " . ($result ? "true" : "false") . "\n";
$zip->close();
echo "done\n";
