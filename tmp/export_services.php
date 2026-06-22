<?php
// Connect to local database and export services
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=agenda_barbearia', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Export services
$services = $pdo->query("SELECT * FROM services WHERE active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
echo "=== SERVICES ===\n";
echo json_encode($services, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

// Also export users (attendants)
$users = $pdo->query("SELECT id, name, email, role FROM users WHERE active = 1 AND role = 'attendant' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
echo "=== ATTENDANTS ===\n";
echo json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

// Also export all users (including admin)
$allUsers = $pdo->query("SELECT id, name, email, role FROM users WHERE active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
echo "=== ALL USERS ===\n";
echo json_encode($allUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
