<?php
$conn = new mysqli('localhost', 'root', '', 'agenda_barbearia');
$conn->set_charset('utf8mb4');

echo '=== TABELA COMPANIES ===' . PHP_EOL;
$result = $conn->query('DESCRIBE companies');
while($row = $result->fetch_assoc()) { echo $row['Field'] . ' | ' . $row['Type'] . PHP_EOL; }

echo PHP_EOL . '=== DADOS COMPANIES ===' . PHP_EOL;
$result = $conn->query('SELECT * FROM companies');
while($row = $result->fetch_assoc()) { echo 'ID: ' . $row['id'] . ' | Nome: ' . $row['name'] . ' | Trial: ' . $row['trial_starts_at'] . ' ate ' . $row['trial_ends_at'] . PHP_EOL; }

echo PHP_EOL . '=== USERS COM COMPANY_ID ===' . PHP_EOL;
$result = $conn->query('SELECT u.id, u.name, u.company_id, c.name as company FROM users u LEFT JOIN companies c ON u.company_id = c.id');
while($row = $result->fetch_assoc()) { echo $row['id'] . ' | ' . $row['name'] . ' | Company: ' . ($row['company'] ?? 'NULL') . PHP_EOL; }

$conn->close();
