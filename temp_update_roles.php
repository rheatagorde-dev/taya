<?php
$path = __DIR__ . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'database.sqlite';
$pdo = new PDO('sqlite:' . $path);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA foreign_keys = OFF');
$pdo->beginTransaction();
$pdo->exec("UPDATE users SET role = 'authorized_user' WHERE role != 'admin'");
$pdo->exec('CREATE TABLE users_new (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    name VARCHAR NOT NULL,
    email VARCHAR NOT NULL UNIQUE,
    password VARCHAR NOT NULL,
    role VARCHAR CHECK ("role" IN (\'admin\', \'authorized_user\')) NOT NULL DEFAULT \'authorized_user\',
    facility_id INTEGER,
    email_verified_at DATETIME,
    remember_token VARCHAR,
    created_at DATETIME,
    updated_at DATETIME
)');
$pdo->exec('INSERT INTO users_new (id, name, email, password, role, facility_id, email_verified_at, remember_token, created_at, updated_at) SELECT id, name, email, password, role, facility_id, email_verified_at, remember_token, created_at, updated_at FROM users');
$pdo->exec('DROP TABLE users');
$pdo->exec('ALTER TABLE users_new RENAME TO users');
$pdo->commit();
$pdo->exec('PRAGMA foreign_keys = ON');
echo "sqlite-role-fix-done\n";
