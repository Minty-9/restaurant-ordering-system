<?php
require __DIR__ . '/../src/database.php';

$pdo->exec(file_get_contents(__DIR__ . '/schema_sqlite.sql'));

$pdo->exec("
INSERT INTO categories (name) VALUES
('Starters'),
('Main Dishes'),
('Drinks');

INSERT INTO menu_items (category_id, name, description, price) VALUES
(1, 'Spring Rolls', 'Crispy rolls', 5.00),
(2, 'Grilled Chicken', 'With fries', 12.50),
(3, 'Fresh Juice', 'Orange', 3.00);
");

$hash = password_hash('admin123', PASSWORD_DEFAULT);
$pdo->exec("
INSERT INTO admin_users (username, password_hash)
VALUES ('admin', '$hash');
");
