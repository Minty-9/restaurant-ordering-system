<?php
require_once __DIR__ . '/../src/bootstrap_sqlite.php';

require "auth.php";
require __DIR__ . "/../../src/database.php";

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $orderId = (int) $_POST['order_id'];
    $status = $_POST['status'];
    if (in_array($status, ['preparing','ready'], true)) {
        $pdo->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$status, $orderId]);
    }
    // return JSON if AJAX
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
        echo json_encode(['success'=>true]);
        exit;
    }
    header("Location: index.php");
    exit;
}

// Fetch today's orders
function fetchOrders($pdo){
    return $pdo->query("
        SELECT *
        FROM orders
        WHERE DATE(created_at) = CURDATE()
        ORDER BY created_at ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
}

$orders = fetchOrders($pdo);
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="assets/css/staff.css">
<title>Kitchen Orders (Today)</title>
</head>
<body>

<header class="staff-header">
    <h1>Kitchen Orders (Today)</h1>
    <nav>
        <a href="menu.php">New Walk-in Order</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div id="orders-board" class="orders-board">
<?php if (!$orders): ?>
    <p class="no-orders">No active orders for today.</p>
<?php else: ?>
    <?php foreach ($orders as $o): ?>
    <div class="order-card status-<?= htmlspecialchars($o['status']) ?>" data-id="<?= $o['id'] ?>">
        <strong><?= htmlspecialchars($o['order_code']) ?></strong>
        <p>Customer: <?= htmlspecialchars($o['customer_name'] ?? 'Walk-in') ?></p>
        <p>Table: <?= htmlspecialchars($o['table_number'] ?? '—') ?></p>
        <p>Status: <?= ucfirst($o['status']) ?></p>
        <p>₦<?= number_format($o['total_amount'],2) ?></p>
        <p class="payment-status <?= $o['payment_status'] ?? 'unpaid' ?>">Payment: <?= ucfirst($o['payment_status'] ?? 'unpaid') ?></p>

        <div class="order-actions">
            <?php if ($o['status'] === 'new'): ?>
            <button onclick="updateStatus(<?= $o['id'] ?>,'preparing')">Start Preparing</button>
            <?php endif; ?>
            <?php if ($o['status'] === 'preparing'): ?>
            <button onclick="updateStatus(<?= $o['id'] ?>,'ready')">Mark Ready</button>
            <?php endif; ?>
            <?php if ($o['status'] === 'ready'): ?>
            <span class="ready-text">Ready</span>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
</div>

<script>
// Auto-refresh every 7 seconds
setInterval(fetchOrders, 7000);

function fetchOrders() {
    fetch('index.php?ajax=1', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newOrders = doc.querySelector('#orders-board');
            if (newOrders) {
                document.querySelector('#orders-board').innerHTML = newOrders.innerHTML;
            }
        });
}

// Update order status via AJAX
function updateStatus(id, status) {
    fetch('index.php', {
        method: 'POST',
        headers: { 'Content-Type':'application/x-www-form-urlencoded', 'X-Requested-With':'XMLHttpRequest' },
        body: `order_id=${id}&status=${status}`
    }).then(res => res.json())
      .then(() => fetchOrders());
}
</script>

</body>
</html>
