<?php
require __DIR__ . '/../src/database.php';

/* 1. Create tables safely */
$pdo->exec(file_get_contents(__DIR__ . '/schema_sqlite.sql'));

/* 2. Seed categories only if empty */
$count = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
if ($count == 0) {
    $pdo->exec("
        INSERT INTO categories (name) VALUES
        ('Starters'),
        ('Main Dishes'),
        ('Drinks')
    ");
}

/* 3. Seed menu items only if empty */
$count = $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();
if ($count == 0) {
    $pdo->exec("
        INSERT INTO menu_items (category_id, name, description, price) VALUES
        (1, 'Spring Rolls', 'Crispy rolls', 5.00),
        (2, 'Grilled Chicken', 'With fries', 12.50),
        (3, 'Fresh Juice', 'Orange', 3.00)
    ");
}

/* 4. Seed admin only if not exists */
$stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = ?");
$stmt->execute(['admin']);

if ($stmt->fetchColumn() == 0) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO admin_users (username, password_hash)
        VALUES (?, ?)
    ");
    $stmt->execute(['admin', $hash]);
}
