<?php
require __DIR__ . "/includes/auth.php";
require __DIR__ . "/../../src/database.php";

$perPage = 10;
$page    = max(1, (int)($_GET['p'] ?? 1));
$offset  = ($page - 1) * $perPage;
$search  = trim($_GET['q'] ?? '');

$where  = '';
$params = [];
if ($search !== '') {
    $where = "WHERE order_code LIKE :q OR customer_name LIKE :q OR customer_phone LIKE :q OR status LIKE :q";
    $params['q'] = "%{$search}%";
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM orders $where");
$countStmt->execute($params);
$totalOrders = (int)$countStmt->fetchColumn();
$totalPages  = max(1, ceil($totalOrders / $perPage));

$sql = "SELECT id, order_code, customer_name, customer_phone, customer_address, total_amount, status, created_at
        FROM orders $where ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue(":$k", $v, PDO::PARAM_STR);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1 class="page-title">Orders</h1>

<form method="GET" class="search-row">
  <input type="hidden" name="page" value="orders">
  <input type="text" name="q" placeholder="Search order code, customer, phone, status..." value="<?= htmlspecialchars($search) ?>">
  <button type="submit" class="btn-primary">Search</button>
  <?php if ($search): ?>
    <a href="index.php?page=orders" class="btn-secondary">Clear</a>
  <?php endif; ?>
</form>

<div class="card" style="padding:0; overflow:hidden;">
  <?php if (empty($orders)): ?>
    <p style="color:#555; font-size:13px; padding:1.5rem;">No orders found.</p>
  <?php else: ?>
  <div style="overflow-x:auto;">
    <table class="data-table">
      <thead>
        <tr>
          <th>Order</th>
          <th>Customer</th>
          <th>Type</th>
          <th>Total</th>
          <th>Status</th>
          <th>Time</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $o):
          $status  = (string)($o['status'] ?? 'new');
          $isOnline = $o['customer_address'] !== null;
        ?>
        <tr>
          <td style="color:#fff; font-weight:500;"><?= htmlspecialchars($o['order_code']) ?></td>
          <td>
            <?= $o['customer_name'] ? htmlspecialchars($o['customer_name']) : '<span style="color:#444;">Walk-in</span>' ?>
            <?php if ($o['customer_phone']): ?>
              <br><span style="color:#555; font-size:11px;"><?= htmlspecialchars($o['customer_phone']) ?></span>
            <?php endif; ?>
          </td>
          <td>
            <span class="badge <?= $isOnline ? 'badge-online' : 'badge-walkin' ?>">
              <?= $isOnline ? 'Online' : 'Walk-in' ?>
            </span>
          </td>
          <td style="color:#f59e0b;">₦<?= number_format((float)$o['total_amount'], 2) ?></td>
          <td><span class="badge badge-<?= htmlspecialchars($status) ?>"><?= ucfirst($status) ?></span></td>
          <td style="color:#555; font-size:12px;"><?= date("d M, H:i", strtotime($o['created_at'])) ?></td>
          <td><a href="index.php?page=order_view&id=<?= (int)$o['id'] ?>" class="btn-view">View</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <div class="pagination" style="padding:1rem 1.25rem; border-top:0.5px solid #2a2a2a;">
    <?php if ($page > 1): ?>
      <a href="?page=orders&p=<?= $page-1 ?>&q=<?= urlencode($search) ?>">← Prev</a>
    <?php endif; ?>
    <span>Page <?= $page ?> of <?= $totalPages ?></span>
    <?php if ($page < $totalPages): ?>
      <a href="?page=orders&p=<?= $page+1 ?>&q=<?= urlencode($search) ?>">Next →</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>
