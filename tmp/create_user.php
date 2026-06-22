<?php
$conn = new mysqli('localhost', 'root', '', 'agenda_barbearia');
$conn->set_charset('utf8mb4');

$name = 'Atendente 2';
$email = 'atendente2@barbearia.com';
$password = password_hash('123456', PASSWORD_DEFAULT);
$role = 'attendant';

$stmt = $conn->prepare('INSERT INTO users (name, email, password, role, active, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())');
$stmt->bind_param('ssss', $name, $email, $password, $role);
$stmt->execute();

echo 'Usuário criado com ID: ' . $conn->insert_id . PHP_EOL;
echo 'Email: ' . $email . PHP_EOL;
echo 'Senha: 123456' . PHP_EOL;
echo PHP_EOL;

$result = $conn->query('SELECT id, name, email, role, active FROM users ORDER BY id');
while($row = $result->fetch_assoc()) {
    echo $row['id'] . ' | ' . $row['name'] . ' | ' . $row['email'] . ' | ' . $row['role'] . PHP_EOL;
}
$conn->close();
