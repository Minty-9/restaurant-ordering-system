<?php
session_start();
require __DIR__ . "/../../src/database.php";

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    header("Location: menu.php");
    exit;
}

/* Table number */
$tableNumber = $_SESSION['table_number'] ?? null;
if (!$tableNumber) {
    die("Table number missing.");
}

/* Defaults */
$customerName = 'Walk-in';

/* Fetch items safely */
$itemIds = array_keys($cart);
$placeholders = implode(',', array_fill(0, count($itemIds), '?'));

$stmt = $pdo->prepare("
    SELECT id, price
    FROM menu_items
    WHERE id IN ($placeholders)
");
$stmt->execute($itemIds);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$items) {
    die("Invalid items.");
}

/* Total */
$totalAmount = 0;
foreach ($items as $item) {
    $totalAmount += $item['price'] * $cart[$item['id']];
}

/* Order */
$orderCode = 'ORD-' . strtoupper(bin2hex(random_bytes(4)));

$stmt = $pdo->prepare("
    INSERT INTO orders
    (
        order_code,
        table_number,
        total_amount,
        status,
        source,
        payment_status
    )
    VALUES (?, ?, ?, 'new', 'walk_in', 'paid')
");

$stmt->execute([
    $orderCode,
    $tableNumber,
    $totalAmount
]);


$orderId = $pdo->lastInsertId();

/* Items */
$stmtItem = $pdo->prepare("
    INSERT INTO order_items
    (order_id, menu_item_id, quantity, price_each)
    VALUES (?, ?, ?, ?)
");

foreach ($items as $item) {
    $stmtItem->execute([
        $orderId,
        $item['id'],
        $cart[$item['id']],
        $item['price']
    ]);
}

unset($_SESSION['cart'], $_SESSION['table_number']);

header("Location: index.php");
exit;

