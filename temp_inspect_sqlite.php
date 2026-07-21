<?php
$path = __DIR__ . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'database.sqlite';
$pdo = new PDO('sqlite:' . $path);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $pdo->query("SELECT sql FROM sqlite_master WHERE name='users'");
echo "SCHEMA:\n" . $stmt->fetchColumn() . "\n\n";
$stmt = $pdo->query("SELECT id, role FROM users ORDER BY id");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "USER: {$row['id']} => {$row['role']}\n";
}
