<?php
$conn = new mysqli('localhost', 'root', '', 'agenda_barbearia');
$conn->set_charset('utf8mb4');

$conn->query("UPDATE services SET name = 'DEPILAÇÃO NARIZ' WHERE id = 8");
$conn->query("UPDATE services SET name = 'DEPILAÇÃO ORELHAS' WHERE id = 9");
$conn->query("UPDATE services SET name = 'RASPAGEM SÓ NA MÁQUINA' WHERE id = 11");

$result = $conn->query('SELECT id, name, price FROM services ORDER BY id');
while($row = $result->fetch_assoc()) {
    echo $row['id'] . ' | ' . $row['name'] . ' | R$ ' . $row['price'] . PHP_EOL;
}
$conn->close();
