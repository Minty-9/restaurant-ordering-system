<?php
require __DIR__ . '/../src/database.php';

$pdo->exec("
CREATE TABLE IF NOT EXISTS admin_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS menu_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER,
    name TEXT NOT NULL,
    description TEXT,
    price REAL NOT NULL,
    is_available INTEGER DEFAULT 1
);

CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_code TEXT UNIQUE,
    customer_name TEXT,
    customer_phone TEXT,
    customer_address TEXT,
    table_number TEXT,
    total_amount REAL NOT NULL,
    status TEXT DEFAULT 'new',
    payment_status TEXT DEFAULT 'paid',
    payment_method TEXT DEFAULT 'online',
    source TEXT DEFAULT 'online',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER,
    menu_item_id INTEGER,
    quantity INTEGER,
    price_each REAL
);
");

$hash = password_hash('admin123', PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
INSERT OR IGNORE INTO admin_users (username, password_hash)
VALUES ('admin', :hash)
");
$stmt->execute(['hash' => $hash]);

$pdo->exec("
INSERT OR IGNORE INTO categories (id, name) VALUES
(1,'Starters'),
(2,'Main'),
(3,'Drinks');

INSERT OR IGNORE INTO menu_items (id, category_id, name, price) VALUES
(1,1,'Spring Rolls',5),
(2,2,'Grilled Chicken',12),
(3,3,'Orange Juice',3);
");
