<?php
$logFile = $_SERVER['DOCUMENT_ROOT'] . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES);
    $recent = array_slice($lines, -30);
    echo '<pre>' . htmlspecialchars(implode("\n", $recent)) . '</pre>';
} else {
    echo 'Arquivo de log nao encontrado';
}
