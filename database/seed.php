<?php
require __DIR__ . '/../src/database.php';

$pdo->exec(file_get_contents(__DIR__ . '/schema_sqlite.sql'));

$pdo->exec("
INSERT INTO categories (name) VALUES
('Starters'),
('Main Dishes'),
('Drinks');
");

$hash = password_hash('admin123', PASSWORD_DEFAULT);
$pdo->exec("
INSERT INTO admin_users (username, password_hash)
VALUES ('admin', '$hash');
");
