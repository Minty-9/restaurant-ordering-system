<?php
session_start();
require __DIR__ . "/../src/database.php";
$cart = $_SESSION['cart'] ?? [];
$items = [];
if (isset($_GET['remove'])) {
    $id = (int) $_GET['remove'];
    unset($_SESSION['cart'][$id]);
    exit;
}
if (!empty($cart)) {
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $stmt = $pdo->query("
        SELECT id, name, price
        FROM menu_items
        WHERE id IN ($ids)
    ");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$grandTotal = 0;
foreach ($items as $item) {
    $grandTotal += $item['price'] * $cart[$item['id']];
}
$cartCount = array_sum($cart);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>The Spot — Cart</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { background: #f5f5f4; font-family: sans-serif; }
    .toast {
      position: fixed;
      bottom: 24px;
      left: 50%;
      transform: translateX(-50%) translateY(80px);
      background: #1a1a1a;
      color: #fff;
      padding: 10px 24px;
      border-radius: 999px;
      font-size: 14px;
      transition: transform 0.3s ease;
      z-index: 99;
    }
    .toast.show { transform: translateX(-50%) translateY(0); }
    .remove-btn:hover { color: #ef4444; }
  </style>
</head>
<body>

<!-- Header -->
<header class="bg-[#1a1a1a] px-6 py-4 flex items-center justify-between sticky top-0 z-10">
  <div class="flex items-center gap-2">
    <span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span>
    <span class="text-white text-lg font-medium tracking-wide">The Spot</span>
  </div>
  <a href="index.php"
     class="flex items-center gap-1 text-gray-400 text-sm hover:text-white transition">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
    </svg>
    Menu
  </a>
</header>

<main class="max-w-2xl mx-auto px-4 py-6 pb-32">

  <p class="text-xs font-medium uppercase tracking-widest text-gray-400 mb-4">Your Order</p>

  <?php if (!$items): ?>
  <!-- Empty state -->
  <div class="flex flex-col items-center justify-center py-24 text-center">
    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 6h13M10 21a1 1 0 100-2 1 1 0 000 2zm7 0a1 1 0 100-2 1 1 0 000 2z"/>
      </svg>
    </div>
    <p class="text-gray-500 text-sm mb-1">Your cart is empty</p>
    <p class="text-gray-400 text-xs mb-6">Add something delicious from the menu</p>
    <a href="index.php"
       class="bg-[#1a1a1a] text-white text-sm font-medium px-6 py-2.5 rounded-full hover:bg-amber-400 hover:text-[#1a1a1a] transition">
      Browse Menu
    </a>
  </div>

  <?php else: ?>
  <!-- Cart items -->
  <div class="flex flex-col gap-3 mb-6" id="cart-list">
    <?php foreach ($items as $item): ?>
    <div class="cart-row bg-white rounded-xl border border-gray-100 px-5 py-4 flex items-center justify-between" data-id="<?= $item['id'] ?>">
      <div class="flex-1">
        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($item['name']) ?></p>
        <p class="text-xs text-gray-400 mt-0.5">
          <?= $cart[$item['id']] ?> × ₦<?= number_format($item['price'], 2) ?>
        </p>
      </div>
      <div class="flex items-center gap-4">
        <p class="text-sm font-medium text-amber-500">
          ₦<?= number_format($item['price'] * $cart[$item['id']], 2) ?>
        </p>
        <button class="remove-btn text-gray-300 transition" data-id="<?= $item['id'] ?>" title="Remove">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Summary card -->
  <div class="bg-white rounded-xl border border-gray-100 px-5 py-4 mb-4">
    <div class="flex justify-between items-center text-xs text-gray-400 mb-2">
      <span>Subtotal</span>
      <span>₦<?= number_format($grandTotal, 2) ?></span>
    </div>
    <div class="flex justify-between items-center text-xs text-gray-400 mb-3">
      <span>Service fee</span>
      <span>₦0.00</span>
    </div>
    <div class="border-t border-gray-100 pt-3 flex justify-between items-center">
      <span class="text-sm font-medium text-gray-900">Total</span>
      <span class="text-base font-medium text-amber-500">₦<?= number_format($grandTotal, 2) ?></span>
    </div>
  </div>

  <!-- Checkout button -->
  <a href="checkout.php"
     class="block w-full bg-[#1a1a1a] text-white text-sm font-medium text-center py-3.5 rounded-full hover:bg-amber-400 hover:text-[#1a1a1a] transition">
    Proceed to Checkout
  </a>

  <?php endif; ?>
</main>

<div class="toast" id="toast"></div>

<script>
function showToast(msg) {
  const toast = document.getElementById('toast');
  toast.textContent = msg;
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 2000);
}

document.querySelectorAll('.remove-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const id = btn.dataset.id;
    const row = document.querySelector(`.cart-row[data-id="${id}"]`);
    fetch(`cart.php?remove=${id}`).then(() => {
      showToast('Item removed');
      if (row) {
        row.style.opacity = '0';
        row.style.transition = 'opacity 0.3s';
        setTimeout(() => {
          row.remove();
          const remaining = document.querySelectorAll('.cart-row');
          if (remaining.length === 0) location.reload();
        }, 300);
      }
    });
  });
});
</script>

</body>
</html>
