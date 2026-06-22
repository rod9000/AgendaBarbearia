<?php
echo "<h3>Teste de Conexão MySQL</h3>";

// Test with InfinityFree internal host
$hosts = ['sql313.infinityfree.com', '127.0.0.1', 'localhost'];
$user = 'if0_41967135';
$pass = 'tiUzMXg7kcrfp';
$db = 'if0_41967135_agenda_estetica';

foreach ($hosts as $h) {
    echo "<p><strong>Tentando $h...</strong></p>";
    try {
        $pdo = new PDO("mysql:host=$h;port=3306;dbname=$db", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $tables = $pdo->query('SHOW TABLES');
        $count = 0;
        echo "<ul>";
        foreach ($tables as $t) {
            echo "<li>" . $t[0] . "</li>";
            $count++;
        }
        echo "</ul>";
        echo "<p style='color:green'>OK - $count tabelas encontradas em $h</p>";
        break;
    } catch (PDOException $e) {
        echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
    }
}
