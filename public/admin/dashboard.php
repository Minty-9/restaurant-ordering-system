<?php
require __DIR__ . "/includes/auth.php";
require __DIR__ . "/../../src/database.php";

$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalItems      = $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();
$totalOrders     = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$todayOrders     = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = DATE('now')")->fetchColumn();
$todayRevenue    = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE DATE(created_at) = DATE('now')")->fetchColumn();

$search = trim($_GET['q'] ?? '');
$isSearch = $search !== '';

if ($isSearch) {
    $stmt = $pdo->prepare("
        SELECT m.id, m.name, m.price, c.name AS category
        FROM menu_items m
        LEFT JOIN categories c ON c.id = m.category_id
        WHERE m.name LIKE :q OR c.name LIKE :q
        ORDER BY c.name, m.name
    ");
    $stmt->execute(['q' => "%$search%"]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $recentOrders = $pdo->query("
        SELECT id, order_code, customer_name, total_amount, status, created_at
        FROM orders
        ORDER BY created_at DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<h1 class="page-title">Dashboard</h1>

<!-- Stat cards -->
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:12px; margin-bottom:1.5rem;">
  <div class="stat-card">
    <div class="stat-label">Today's Orders</div>
    <div class="stat-value"><?= $todayOrders ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Today's Revenue</div>
    <div class="stat-value" style="font-size:22px;">₦<?= number_format((float)$todayRevenue, 2) ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Total Orders</div>
    <div class="stat-value"><?= $totalOrders ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Menu Items</div>
    <div class="stat-value"><?= $totalItems ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Categories</div>
    <div class="stat-value"><?= $totalCategories ?></div>
  </div>
</div>

<!-- Search -->
<form method="GET" class="search-row">
  <input type="hidden" name="page" value="dashboard">
  <input type="text" name="q" placeholder="Search menu items or categories..." value="<?= htmlspecialchars($search) ?>">
  <button type="submit" class="btn-primary">Search</button>
  <?php if ($isSearch): ?>
    <a href="index.php?page=dashboard" class="btn-secondary">Clear</a>
  <?php endif; ?>
</form>

<?php if ($isSearch): ?>
<!-- Search results -->
<div class="card">
  <h2>Results for "<?= htmlspecialchars($search) ?>"</h2>
  <?php if (!$items): ?>
    <p style="color:#555; font-size:13px;">No items found.</p>
  <?php else: ?>
  <table class="data-table">
    <thead>
      <tr><th>Name</th><th>Category</th><th>Price</th></tr>
    </thead>
    <tbody>
      <?php foreach ($items as $i): ?>
      <tr>
        <td><?= htmlspecialchars($i['name']) ?></td>
        <td><?= htmlspecialchars($i['category']) ?></td>
        <td style="color:#f59e0b;">₦<?= number_format($i['price'], 2) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<?php else: ?>
<!-- Recent orders -->
<div class="card">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
    <h2 style="margin-bottom:0;">Recent Orders</h2>
    <a href="index.php?page=orders" class="btn-view">View all</a>
  </div>
  <?php if (!$recentOrders): ?>
    <p style="color:#555; font-size:13px;">No orders yet.</p>
  <?php else: ?>
  <table class="data-table">
    <thead>
      <tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th>Time</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($recentOrders as $o): ?>
      <tr>
        <td style="color:#fff; font-weight:500;"><?= htmlspecialchars($o['order_code']) ?></td>
        <td><?= $o['customer_name'] ? htmlspecialchars($o['customer_name']) : '<span style="color:#444;">Walk-in</span>' ?></td>
        <td style="color:#f59e0b;">₦<?= number_format($o['total_amount'], 2) ?></td>
        <td><span class="badge badge-<?= htmlspecialchars($o['status']) ?>"><?= ucfirst($o['status']) ?></span></td>
        <td style="color:#555;"><?= date("d M, H:i", strtotime($o['created_at'])) ?></td>
        <td><a href="index.php?page=order_view&id=<?= $o['id'] ?>" class="btn-view">View</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
<?php endif; ?>
