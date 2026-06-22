<?php
$output = "Teste de Conexao MySQL\n";
$output .= "========================\n";

$hosts = ['sql313.infinityfree.com', '127.0.0.1', 'localhost'];
$user = 'if0_41967135';
$pass = 'tiUzMXg7kcrfp';
$db = 'if0_41967135_agenda_estetica';

foreach ($hosts as $h) {
    $output .= "Tentando $h...\n";
    try {
        $pdo = new PDO("mysql:host=$h;port=3306;dbname=$db", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $tables = $pdo->query('SHOW TABLES');
        $count = 0;
        foreach ($tables as $t) { $count++; }
        $output .= "OK - $count tabelas encontradas em $h\n";
        file_put_contents(__DIR__ . '/test_db_result.txt', $output);
        echo nl2br($output);
        exit;
    } catch (PDOException $e) {
        $output .= "Erro: " . $e->getMessage() . "\n";
    }
}

$output .= "\nTODAS AS TENTATIVAS FALHARAM\n";
file_put_contents(__DIR__ . '/test_db_result.txt', $output);
echo nl2br($output);
