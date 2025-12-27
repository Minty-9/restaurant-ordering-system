<?php
require __DIR__ . "/includes/auth.php";
require __DIR__ . "/../src/database.php";

/* =========================
   CONFIG
========================= */
$perPage = 10;
$page = max(1, (int)($_GET['p'] ?? 1));
$offset = ($page - 1) * $perPage;
$search = trim($_GET['q'] ?? '');

/* =========================
   BASE SQL
========================= */
$where = '';
$params = [];

if ($search !== '') {
    $where = "
        WHERE 
            order_code LIKE :q
            OR customer_name LIKE :q
            OR customer_phone LIKE :q
            OR status LIKE :q
    ";
    $params['q'] = "%{$search}%";
}

/* =========================
   TOTAL COUNT
========================= */
$countStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM orders
    $where
");
$countStmt->execute($params);
$totalOrders = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalOrders / $perPage));

/* =========================
   FETCH ORDERS
========================= */
$sql = "
    SELECT 
        id,
        order_code,
        customer_name,
        customer_phone,
        customer_address,
        total_amount,
        status,
        created_at
    FROM orders
    $where
    ORDER BY created_at DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);

foreach ($params as $k => $v) {
    $stmt->bindValue(":$k", $v, PDO::PARAM_STR);
}

$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content">
    <h1 class="page-title">Orders</h1>

    <form method="GET" class="search-bar">
        <input type="hidden" name="page" value="orders">
        <input
            type="text"
            name="q"
            placeholder="Search order code, customer, phone, status..."
            value="<?= htmlspecialchars($search) ?>"
        >
        <button type="submit">Search</button>
    </form>

<?php if (empty($orders)): ?>
    <p>No orders found.</p>
<?php else: ?>

<div class="orders-wrapper">
<table class="table orders-table">
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
<?php foreach ($orders as $o): ?>
<?php
    $name    = $o['customer_name'] ?? null;
    $phone   = $o['customer_phone'] ?? null;
    $address = $o['customer_address'] ?? null;
    $status  = (string)($o['status'] ?? 'new');
?>
<tr>
    <td><?= htmlspecialchars((string)$o['order_code']) ?></td>

    <td>
        <?= $name === null ? '<em>Walk-in customer</em>' : htmlspecialchars($name) ?>
        <?php if ($phone !== null): ?>
            <br><small><?= htmlspecialchars($phone) ?></small>
        <?php endif; ?>
    </td>

    <td>
        <?php if ($address === null): ?>
            <span class="badge walkin">Walk-in</span>
        <?php else: ?>
            <span class="badge online">Online</span>
        <?php endif; ?>
    </td>

    <td>₦<?= number_format((float)$o['total_amount'], 2) ?></td>

    <td>
        <span class="status <?= htmlspecialchars($status) ?>">
            <?= ucfirst($status) ?>
        </span>
    </td>

    <td><?= date("d M, H:i", strtotime($o['created_at'])) ?></td>

    <td>
        <a href="index.php?page=order_view&id=<?= (int)$o['id'] ?>"
           class="btn-primary-small">View</a>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<!-- ======================
     PAGINATION
====================== -->
<div class="pagination">
<?php if ($page > 1): ?>
    <a href="?page=orders&p=<?= $page - 1 ?>&q=<?= urlencode($search) ?>">← Prev</a>
<?php endif; ?>

<span>Page <?= $page ?> of <?= $totalPages ?></span>

<?php if ($page < $totalPages): ?>
    <a href="?page=orders&p=<?= $page + 1 ?>&q=<?= urlencode($search) ?>">Next →</a>
<?php endif; ?>
</div>

<?php endif; ?>
</div>
