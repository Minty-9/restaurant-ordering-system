<?php
require __DIR__ . "/includes/auth.php";
require __DIR__ . "/../src/database.php";

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Order not found.");
}

/* Normalize values safely */
$orderCode = (string)($order['order_code'] ?? '');
$name      = $order['customer_name'] ?? null;
$phone     = $order['customer_phone'] ?? null;
$address   = $order['customer_address'] ?? null;
$status    = (string)($order['status'] ?? 'new');

/* Fetch order items */
$stmt = $pdo->prepare("
    SELECT 
        oi.quantity,
        oi.price_each,
        m.name
    FROM order_items oi
    JOIN menu_items m ON m.id = oi.menu_item_id
    WHERE oi.order_id = ?
");
$stmt->execute([$id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Update status */
$allowedStatuses = ['new','preparing','ready','completed','cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['status'] ?? '';

    if (in_array($newStatus, $allowedStatuses, true)) {
        $pdo->prepare("UPDATE orders SET status=? WHERE id=?")
            ->execute([$newStatus, $id]);
    }

    header("Location: index.php?page=order_view&id=$id");
    exit;
}
?>

<div class="main-content">

    <a href="index.php?page=orders" class="btn-primary-small">← Back to Orders</a>

    <h1 class="page-title">
        Order <?= htmlspecialchars($orderCode) ?>
    </h1>

    <div class="card">
        <p>
            <strong>Customer:</strong>
            <?= $name === null ? '—' : htmlspecialchars($name) ?>
        </p>

        <p>
            <strong>Phone:</strong>
            <?= $phone === null ? '—' : htmlspecialchars($phone) ?>
        </p>

        <p>
            <strong>Type:</strong>
            <?php if ($address === null): ?>
                <span class="badge walkin">Walk-in</span>
            <?php else: ?>
                <span class="badge online">Online</span>
            <?php endif; ?>
        </p>

        <?php if ($address !== null): ?>
            <p>
                <strong>Address:</strong><br>
                <?= htmlspecialchars($address) ?>
            </p>
        <?php endif; ?>

        <p>
            <strong>Status:</strong>
            <?= ucfirst(htmlspecialchars($status)) ?>
        </p>

        <p>
            <strong>Placed:</strong>
            <?= date("d M Y, H:i", strtotime($order['created_at'])) ?>
        </p>
    </div>

    <div class="card">
        <h2>Items</h2>

        <table class="table orders-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $i):
                $subtotal = $i['quantity'] * $i['price_each'];
            ?>
                <tr>
                    <td><?= htmlspecialchars($i['name']) ?></td>
                    <td><?= (int)$i['quantity'] ?></td>
                    <td>₦<?= number_format((float)$i['price_each'], 2) ?></td>
                    <td>₦<?= number_format($subtotal, 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <p style="margin-top:15px;">
            <strong>Total:</strong>
            ₦<?= number_format((float)$order['total_amount'], 2) ?>
        </p>
    </div>

    <div class="card">
        <h2>Update Status</h2>

        <form method="POST">
            <select name="status">
                <?php foreach ($allowedStatuses as $s): ?>
                    <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>>
                        <?= ucfirst($s) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button class="btn-primary">Update</button>
        </form>
    </div>

</div>
