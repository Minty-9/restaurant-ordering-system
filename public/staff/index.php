<?php
require_once __DIR__ . '/../../src/bootstrap_sqlite.php';
require "auth.php";
require __DIR__ . "/../../src/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $orderId = (int) $_POST['order_id'];
    $status = $_POST['status'];
    if (in_array($status, ['preparing','ready'], true)) {
        $pdo->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$status, $orderId]);
    }
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(['success' => true]);
        exit;
    }
    header("Location: index.php");
    exit;
}

function fetchOrders($pdo) {
    return $pdo->query("
        SELECT * FROM orders
        WHERE DATE(created_at) = DATE('now')
        ORDER BY created_at ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
}
$orders = fetchOrders($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>The Spot — Kitchen</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { background: #111; font-family: sans-serif; }

    .order-card {
      background: #1e1e1e;
      border: 0.5px solid #2e2e2e;
      border-radius: 16px;
      padding: 1.25rem;
      display: flex;
      flex-direction: column;
      gap: 8px;
      transition: border-color 0.3s;
    }

    /* Status border accents */
    .status-new     { border-left: 3px solid #f59e0b; }
    .status-preparing { border-left: 3px solid #3b82f6; }
    .status-ready   { border-left: 3px solid #22c55e; }

    /* Status badge */
    .badge {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .badge-new        { background: #f59e0b22; color: #f59e0b; }
    .badge-preparing  { background: #3b82f622; color: #60a5fa; }
    .badge-ready      { background: #22c55e22; color: #4ade80; }
    .badge-paid       { background: #22c55e22; color: #4ade80; }
    .badge-unpaid     { background: #ef444422; color: #f87171; }

    /* Action buttons */
    .action-btn {
      width: 100%;
      padding: 10px;
      border-radius: 10px;
      font-size: 13px;
      font-weight: 500;
      border: none;
      cursor: pointer;
      transition: opacity 0.2s;
    }
    .action-btn:hover { opacity: 0.85; }
    .btn-prepare { background: #3b82f6; color: #fff; }
    .btn-ready   { background: #22c55e; color: #fff; }

    /* Live pulse dot */
    @keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:0.3; } }
    .live-dot { animation: pulse 1.5s ease-in-out infinite; }
  </style>
</head>
<body class="min-h-screen">

<!-- Header -->
<header class="bg-[#1a1a1a] border-b border-[#2a2a2a] px-6 py-4 flex items-center justify-between sticky top-0 z-10">
  <div class="flex items-center gap-3">
    <div class="flex items-center gap-2">
      <span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span>
      <span class="text-white text-base font-medium tracking-wide">The Spot</span>
    </div>
    <span class="text-gray-600 text-sm">/ Kitchen</span>
    <!-- Live indicator -->
    <div class="flex items-center gap-1.5 ml-2">
      <span class="live-dot w-2 h-2 rounded-full bg-green-500 inline-block"></span>
      <span class="text-green-500 text-xs">Live</span>
    </div>
  </div>
  <nav class="flex items-center gap-4">
    <a href="menu.php"
       class="text-sm text-gray-400 hover:text-white transition flex items-center gap-1.5">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
      </svg>
      New Order
    </a>
    <a href="logout.php"
       class="text-sm text-gray-500 hover:text-red-400 transition">
      Logout
    </a>
  </nav>
</header>

<!-- Stats bar -->
<?php
$newCount       = count(array_filter($orders, fn($o) => $o['status'] === 'new'));
$preparingCount = count(array_filter($orders, fn($o) => $o['status'] === 'preparing'));
$readyCount     = count(array_filter($orders, fn($o) => $o['status'] === 'ready'));
?>
<div class="border-b border-[#2a2a2a] bg-[#161616] px-6 py-3 flex gap-6">
  <div class="flex items-center gap-2">
    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
    <span class="text-gray-400 text-xs">New <strong class="text-white ml-1"><?= $newCount ?></strong></span>
  </div>
  <div class="flex items-center gap-2">
    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
    <span class="text-gray-400 text-xs">Preparing <strong class="text-white ml-1"><?= $preparingCount ?></strong></span>
  </div>
  <div class="flex items-center gap-2">
    <span class="w-2 h-2 rounded-full bg-green-500"></span>
    <span class="text-gray-400 text-xs">Ready <strong class="text-white ml-1"><?= $readyCount ?></strong></span>
  </div>
  <div class="ml-auto text-gray-600 text-xs">Today's orders: <?= count($orders) ?></div>
</div>

<!-- Orders board -->
<div class="p-6">
  <?php if (!$orders): ?>
  <div class="flex flex-col items-center justify-center py-24 text-center">
    <p class="text-gray-600 text-sm">No orders yet today.</p>
    <p class="text-gray-700 text-xs mt-1">New orders will appear here automatically.</p>
  </div>
  <?php else: ?>
  <div id="orders-board" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    <?php foreach ($orders as $o): ?>
    <div class="order-card status-<?= htmlspecialchars($o['status']) ?>" data-id="<?= $o['id'] ?>">

      <!-- Order header -->
      <div class="flex items-center justify-between">
        <span class="text-white font-medium text-sm"><?= htmlspecialchars($o['order_code']) ?></span>
        <span class="badge badge-<?= htmlspecialchars($o['status']) ?>">
          <?= ucfirst($o['status']) ?>
        </span>
      </div>

      <!-- Divider -->
      <div class="border-t border-[#2e2e2e]"></div>

      <!-- Order details -->
      <div class="flex flex-col gap-1.5">
        <div class="flex justify-between items-center">
          <span class="text-gray-500 text-xs">Customer</span>
          <span class="text-gray-300 text-xs font-medium"><?= htmlspecialchars($o['customer_name'] ?? 'Walk-in') ?></span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-gray-500 text-xs">Table</span>
          <span class="text-gray-300 text-xs font-medium"><?= htmlspecialchars($o['table_number'] ?? '—') ?></span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-gray-500 text-xs">Amount</span>
          <span class="text-amber-400 text-xs font-medium">₦<?= number_format($o['total_amount'], 2) ?></span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-gray-500 text-xs">Payment</span>
          <span class="badge badge-<?= $o['payment_status'] ?? 'unpaid' ?>">
            <?= ucfirst($o['payment_status'] ?? 'unpaid') ?>
          </span>
        </div>
      </div>

      <!-- Action button -->
      <?php if ($o['status'] === 'new'): ?>
      <button class="action-btn btn-prepare mt-1" onclick="updateStatus(<?= $o['id'] ?>, 'preparing')">
        Start Preparing
      </button>
      <?php elseif ($o['status'] === 'preparing'): ?>
      <button class="action-btn btn-ready mt-1" onclick="updateStatus(<?= $o['id'] ?>, 'ready')">
        Mark as Ready
      </button>
      <?php else: ?>
      <div class="w-full py-2.5 rounded-xl bg-green-500/10 text-green-400 text-xs font-medium text-center mt-1">
        Order Ready ✓
      </div>
      <?php endif; ?>

    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<script>
setInterval(fetchOrders, 7000);

function fetchOrders() {
  fetch('index.php?ajax=1', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(res => res.text())
    .then(html => {
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      const newBoard = doc.querySelector('#orders-board');
      if (newBoard) document.querySelector('#orders-board').innerHTML = newBoard.innerHTML;
    });
}

function updateStatus(id, status) {
  fetch('index.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: `order_id=${id}&status=${status}`
  })
  .then(res => res.json())
  .then(() => fetchOrders());
}
</script>

</body>
</html>
