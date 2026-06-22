<?php
$conn = new mysqli('localhost', 'root', '', 'agenda_barbearia');
$conn->set_charset('utf8mb4');

$conn->query("INSERT INTO companies (name, slug, email, phone, trial_starts_at, trial_ends_at, active, created_at, updated_at) VALUES ('Barbearia Andrê', 'barbearia-andre', 'contato@barbeariaandre.com', '(44) 99713-5071', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 1, NOW(), NOW())");

$companyId = $conn->insert_id;
echo 'Empresa criada com ID: ' . $companyId . PHP_EOL;

$conn->query('UPDATE users SET company_id = ' . $companyId);
echo 'Usuários vinculados à empresa' . PHP_EOL;

$result = $conn->query('SELECT u.id, u.name, u.company_id, c.name as company_name FROM users u LEFT JOIN companies c ON u.company_id = c.id');
while($row = $result->fetch_assoc()) {
    echo $row['id'] . ' | ' . $row['name'] . ' | Company: ' . ($row['company_name'] ?? 'NULL') . PHP_EOL;
}

$conn->close();
