<?php
$zipFile = __DIR__ . '/../vendor_prod.zip';
$extractTo = __DIR__ . '/..';

if (!file_exists($zipFile)) {
    die("Arquivo vendor_prod.zip não encontrado em: " . $zipFile);
}

$zip = new ZipArchive();
$res = $zip->open($zipFile);
if ($res === TRUE) {
    $zip->extractTo($extractTo);
    $zip->close();
    echo "Extração concluída! " . count(glob($extractTo . '/vendor/*')) . " pacotes extraídos.";
    @unlink($zipFile);
    @unlink(__FILE__);
    echo " Arquivos de instalação removidos.";
} else {
    echo "Falha ao extrair. Código: " . $res;
}
