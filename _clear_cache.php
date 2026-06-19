<?php
// Emergency cache cleanup - DELETE AFTER USE
$cacheFiles = [
    __DIR__ . '/bootstrap/cache/packages.php',
    __DIR__ . '/bootstrap/cache/services.php',
];

$deleted = 0;
foreach ($cacheFiles as $file) {
    if (file_exists($file)) {
        unlink($file);
        echo "Deletado: " . basename($file) . "<br>";
        $deleted++;
    }
}

if ($deleted === 0) {
    echo "Nenhum arquivo de cache encontrado. O site ja deve estar funcionando.<br>";
} else {
    echo "<br>Caches limpos! O site deve voltar a funcionar.<br>";
}

echo "<p style='color:red'>⚠ DELETE ESTE ARQUIVO APOS USAR</p>";
