<?php
$conn = new mysqli('localhost', 'root', '', 'agenda_barbearia');
$conn->set_charset('utf8mb4');

echo "=== EMPRESAS ===" . PHP_EOL;
$result = $conn->query('SELECT id, name, trial_starts_at, trial_ends_at, active FROM companies');
while($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']} | Nome: {$row['name']} | Início: {$row['trial_starts_at']} | Fim: {$row['trial_ends_at']} | Ativo: {$row['active']}" . PHP_EOL;
}

echo PHP_EOL . "=== USUÁRIOS ===" . PHP_EOL;
$result = $conn->query('SELECT u.id, u.name, u.email, u.company_id, c.name as company_name FROM users u LEFT JOIN companies c ON u.company_id = c.id');
while($row = $result->fetch_assoc()) {
    echo "{$row['id']} | {$row['name']} | {$row['email']} | Company: " . ($row['company_name'] ?? 'NULL') . PHP_EOL;
}

$conn->close();
