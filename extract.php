<?php
$zipFile = __DIR__ . '/deploy.zip';
$extractTo = __DIR__;

if (!file_exists($zipFile)) {
    die("Arquivo deploy.zip não encontrado.");
}

$zip = new ZipArchive();
$res = $zip->open($zipFile);
if ($res === TRUE) {
    $zip->extractTo($extractTo);
    $zip->close();
    echo "Extração concluída com sucesso!<br>";
    echo "Total: " . count(glob(__DIR__ . '/*')) . " arquivos extraídos.";
    
    // Remove o zip e este script
    @unlink($zipFile);
    @unlink(__FILE__);
} else {
    echo "Falha ao extrair o arquivo zip. Código: " . $res;
}
?>