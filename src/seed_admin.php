<?php
require __DIR__ . '/database.php';

$username = 'admin';
$password = password_hash('admin123', PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    "INSERT OR IGNORE INTO admin_users (username, password_hash)
     VALUES (?, ?)"
);
$stmt->execute([$username, $password]);

echo "Admin user created";
