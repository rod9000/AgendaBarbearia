<?php
@set_time_limit(300);
$vendorPath = __DIR__ . '/../vendor';
$zipFile = __DIR__ . '/../vendor_prod.zip';

if (!file_exists($zipFile)) {
    die("vendor_prod.zip not found");
}

$zip = new ZipArchive();
$res = $zip->open($zipFile);
if ($res !== TRUE) {
    die("Failed to open zip. Code: $res\n");
}

echo "Extracting " . $zip->numFiles . " files...\n";
$zip->extractTo(__DIR__ . '/..');
$zip->close();
echo "Extraction complete!\n";

// Clean up
@unlink($zipFile);
// Don't self-delete in case we need to run again
echo "Done.\n";
