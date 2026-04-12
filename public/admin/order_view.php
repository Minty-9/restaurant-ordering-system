<?php
require __DIR__ . "/includes/auth.php";
require __DIR__ . "/../../src/database.php";

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) die("Order not found.");

$orderCode = (string)($order['order_code'] ?? '');
$name      = $order['customer_name'] ?? null;
$phone     = $order['customer_phone'] ?? null;
$address   = $order['customer_address'] ?? null;
$status    = (string)($order['status'] ?? 'new');

$stmt = $pdo->prepare("
    SELECT oi.quantity, oi.price_each, m.name
    FROM order_items oi
    JOIN menu_items m ON m.id = oi.menu_item_id
    WHERE oi.order_id = ?
");
$stmt->execute([$id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$allowedStatuses = ['new','preparing','ready','completed','cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['status'] ?? '';
    if (in_array($newStatus, $allowedStatuses, true)) {
        $pdo->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$newStatus, $id]);
    }
    header("Location: index.php?page=order_view&id=$id");
    exit;
}
?>

<div style="margin-bottom:1.5rem;">
  <a href="index.php?page=orders" class="btn-secondary">← Back to Orders</a>
</div>

<div style="display:flex; align-items:center; gap:12px; margin-bottom:1.5rem; flex-wrap:wrap;">
  <h1 class="page-title" style="margin-bottom:0;">Order <?= htmlspecialchars($orderCode) ?></h1>
  <span class="badge badge-<?= htmlspecialchars($status) ?>"><?= ucfirst($status) ?></span>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">

  <!-- Customer info -->
  <div class="card" style="margin-bottom:0;">
    <h2>Customer Details</h2>
    <table style="width:100%; font-size:13px; border-collapse:collapse;">
      <tr>
        <td style="color:#555; padding:6px 0; width:40%;">Name</td>
        <td style="color:#ccc;"><?= $name ?? '<span style="color:#444;">—</span>' ?></td>
      </tr>
      <tr>
        <td style="color:#555; padding:6px 0;">Phone</td>
        <td style="color:#ccc;"><?= $phone ?? '<span style="color:#444;">—</span>' ?></td>
      </tr>
      <tr>
        <td style="color:#555; padding:6px 0;">Type</td>
        <td>
          <span class="badge <?= $address !== null ? 'badge-online' : 'badge-walkin' ?>">
            <?= $address !== null ? 'Online' : 'Walk-in' ?>
          </span>
        </td>
      </tr>
      <?php if ($address): ?>
      <tr>
        <td style="color:#555; padding:6px 0; vertical-align:top;">Address</td>
        <td style="color:#ccc;"><?= htmlspecialchars($address) ?></td>
      </tr>
      <?php endif; ?>
      <tr>
        <td style="color:#555; padding:6px 0;">Placed</td>
        <td style="color:#ccc;"><?= date("d M Y, H:i", strtotime($order['created_at'])) ?></td>
      </tr>
    </table>
  </div>

  <!-- Update status -->
  <div class="card" style="margin-bottom:0;">
    <h2>Update Status</h2>
    <form method="POST" style="display:flex; flex-direction:column; gap:12px;">
      <select name="status" style="width:100%;">
        <?php foreach ($allowedStatuses as $s): ?>
          <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn-primary" style="width:100%; text-align:center;">Update Status</button>
    </form>
  </div>

</div>

<!-- Order items -->
<div class="card">
  <h2>Items Ordered</h2>
  <table class="data-table">
    <thead>
      <tr><th>Item</th><th>Qty</th><th>Unit Price</th><th>Subtotal</th></tr>
    </thead>
    <tbody>
      <?php foreach ($items as $i):
        $subtotal = $i['quantity'] * $i['price_each'];
      ?>
      <tr>
        <td><?= htmlspecialchars($i['name']) ?></td>
        <td><?= (int)$i['quantity'] ?></td>
        <td>₦<?= number_format((float)$i['price_each'], 2) ?></td>
        <td style="color:#f59e0b; font-weight:500;">₦<?= number_format($subtotal, 2) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div style="border-top:0.5px solid #2a2a2a; margin-top:1rem; padding-top:1rem; display:flex; justify-content:flex-end; align-items:center; gap:12px;">
    <span style="color:#555; font-size:13px;">Order Total</span>
    <span style="color:#f59e0b; font-size:18px; font-weight:500;">₦<?= number_format((float)$order['total_amount'], 2) ?></span>
  </div>
</div>
