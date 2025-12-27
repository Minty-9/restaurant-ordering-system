<?php
session_start();
require __DIR__ . "/../src/database.php";

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    header("Location: cart.php");
    exit;
}

$source = 'online';

/* Fake payment check */
if (($_POST['fake_payment'] ?? '0') !== '1') {
    die('Payment not completed.');
}

/* Customer info */
$customerName  = trim($_POST['customer_name'] ?? '');
$customerPhone = trim($_POST['customer_phone'] ?? '');
$customerAddress = trim($_POST['customer_address'] ?? '');

if ($customerName === '' || $customerPhone === '') {
    die('Customer details required.');
}

/* Fetch items */
$itemIds = array_keys($cart);
$placeholders = implode(',', array_fill(0, count($itemIds), '?'));

$stmt = $pdo->prepare("
    SELECT id, price
    FROM menu_items
    WHERE id IN ($placeholders)
");
$stmt->execute($itemIds);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Calculate total */
$totalAmount = 0;
foreach ($items as $item) {
    $totalAmount += $item['price'] * $cart[$item['id']];
}

/* Create order */
$orderCode = 'ORD-' . strtoupper(bin2hex(random_bytes(4)));

$stmt = $pdo->prepare("
    INSERT INTO orders
    (order_code, customer_name, customer_phone, customer_address,
     total_amount, status, source, payment_status)
    VALUES (?, ?, ?, ?, ?, 'new', 'online', 'paid')
");

$stmt->execute([
    $orderCode,
    $customerName,
    $customerPhone,
    $customerAddress,
    $totalAmount
]);

$orderId = $pdo->lastInsertId();

/* Insert items */
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

unset($_SESSION['cart']);

header("Location: order_success.php?code=$orderCode");
exit;
