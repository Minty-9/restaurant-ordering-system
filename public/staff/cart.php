<?php
require "auth.php";
require __DIR__ . "/../../src/database.php";
$cart = $_SESSION['cart'] ?? [];

if (isset($_GET['remove'])) {
    unset($cart[$_GET['remove']]);
    $_SESSION['cart'] = $cart;
    header("Location: cart.php?removed=1");
    exit;
}

if (!$cart) {
    header("Location: menu.php");
    exit;
}

$ids = implode(',', array_keys($cart));
$items = $pdo->query("
    SELECT id, name, price
    FROM menu_items
    WHERE id IN ($ids)
")->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
foreach ($items as $i) {
    $total += $i['price'] * $cart[$i['id']];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>The Spot — Staff Cart</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { background: #111; font-family: sans-serif; }

    .toast {
      position: fixed;
      bottom: 24px;
      left: 50%;
      transform: translateX(-50%) translateY(80px);
      background: #1e1e1e;
      border: 0.5px solid #2e2e2e;
      color: #fff;
      padding: 10px 24px;
      border-radius: 999px;
      font-size: 13px;
      transition: transform 0.3s ease;
      z-index: 99;
    }
    .toast.show { transform: translateX(-50%) translateY(0); }

    .remove-btn { transition: color 0.2s; }
    .remove-btn:hover { color: #ef4444; }
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
    <span class="text-gray-600 text-sm">/ Cart</span>
    <?php if (isset($_SESSION['table_number'])): ?>
    <span class="bg-amber-400/10 text-amber-400 text-xs font-medium px-3 py-1 rounded-full border border-amber-400/20">
      Table <?= htmlspecialchars($_SESSION['table_number']) ?>
    </span>
    <?php endif; ?>
  </div>
  <a href="menu.php"
     class="flex items-center gap-1 text-gray-400 text-sm hover:text-white transition">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
    </svg>
    Back to Menu
  </a>
</header>

<main class="max-w-2xl mx-auto px-4 py-6 pb-32">

  <p class="text-xs font-medium uppercase tracking-widest text-gray-600 mb-4">Order Summary</p>

  <!-- Cart items -->
  <div class="flex flex-col gap-3 mb-6">
    <?php foreach ($items as $i):
      $qty = $cart[$i['id']];
      $subtotal = $i['price'] * $qty;
    ?>
    <div class="bg-[#1e1e1e] border border-[#2e2e2e] rounded-xl px-5 py-4 flex items-center justify-between">
      <div class="flex-1">
        <p class="text-white text-sm font-medium"><?= htmlspecialchars($i['name']) ?></p>
        <p class="text-gray-500 text-xs mt-0.5">
          <?= $qty ?> × ₦<?= number_format($i['price'], 2) ?>
        </p>
      </div>
      <div class="flex items-center gap-4">
        <p class="text-amber-400 text-sm font-medium">₦<?= number_format($subtotal, 2) ?></p>
        <a href="?remove=<?= $i['id'] ?>"
           class="remove-btn text-gray-600"
           title="Remove item">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Summary card -->
  <div class="bg-[#1e1e1e] border border-[#2e2e2e] rounded-xl px-5 py-4 mb-4">
    <div class="flex justify-between items-center text-xs text-gray-500 mb-2">
      <span>Items</span>
      <span><?= array_sum($cart) ?></span>
    </div>
    <div class="flex justify-between items-center text-xs text-gray-500 mb-3">
      <span>Service fee</span>
      <span>₦0.00</span>
    </div>
    <div class="border-t border-[#2e2e2e] pt-3 flex justify-between items-center">
      <span class="text-white text-sm font-medium">Total</span>
      <span class="text-amber-400 text-base font-medium">₦<?= number_format($total, 2) ?></span>
    </div>
  </div>

  <!-- Place order button -->
  <a href="place_order.php"
     class="block w-full bg-amber-400 text-[#1a1a1a] text-sm font-medium text-center py-3.5 rounded-full hover:bg-amber-300 transition">
    Place Order
  </a>

  <!-- Back to menu link -->
  <a href="menu.php"
     class="block w-full text-center text-gray-600 text-sm mt-4 hover:text-gray-400 transition">
    + Add more items
  </a>

</main>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script>
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('removed') === '1') {
  const toast = document.getElementById('toast');
  toast.textContent = 'Item removed from cart';
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 2000);
  history.replaceState(null, '', 'cart.php');
}
</script>

</body>
</html>
