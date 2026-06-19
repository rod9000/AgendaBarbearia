<?php
$vendorPath = __DIR__ . '/../vendor';
$zipFile = __DIR__ . '/../vendor_prod.zip';

// Delete old vendor recursively
function rrmdir($dir) {
    if (!is_dir($dir)) return;
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item == '.' || $item == '..') continue;
        $path = $dir . '/' . $item;
        is_dir($path) ? rrmdir($path) : unlink($path);
    }
    rmdir($dir);
}

echo "Deleting old vendor...\n";
rrmdir($vendorPath);
echo "Done.\n";

// Extract new zip
if (!file_exists($zipFile)) {
    die("vendor_prod.zip not found at: $zipFile");
}

$zip = new ZipArchive();
$res = $zip->open($zipFile);
if ($res === TRUE) {
    $zip->extractTo(__DIR__ . '/..');
    $zip->close();
    echo "Extraction complete!\n";
    @unlink($zipFile);
    @unlink(__FILE__);
    echo "Cleanup done.\n";
} else {
    echo "Failed to open zip. Code: $res\n";
}
