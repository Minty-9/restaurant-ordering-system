<?php
session_start();
require __DIR__ . "/../src/database.php";
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header("Location: cart.php");
    exit;
}
$itemIds = array_keys($cart);
$placeholders = implode(',', array_fill(0, count($itemIds), '?'));
$stmt = $pdo->prepare("
    SELECT id, name, price
    FROM menu_items
    WHERE id IN ($placeholders)
");
$stmt->execute($itemIds);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
$grandTotal = 0;
foreach ($items as $item) {
    $grandTotal += $item['price'] * $cart[$item['id']];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>The Spot — Checkout</title>
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

    input[type="text"], input[type="tel"], textarea {
      width: 100%;
      background: #fff;
      border: 0.5px solid #e5e7eb;
      border-radius: 12px;
      padding: 12px 16px;
      font-size: 14px;
      color: #111;
      outline: none;
      transition: border-color 0.2s;
      font-family: sans-serif;
    }
    input[type="text"]:focus, input[type="tel"]:focus, textarea:focus {
      border-color: #f59e0b;
    }
    textarea { resize: none; height: 90px; }

    /* Payment overlay */
    #paymentOverlay {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.6);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 50;
    }
    .payment-box {
      background: #fff;
      border-radius: 20px;
      padding: 2rem 2.5rem;
      text-align: center;
      max-width: 280px;
      width: 90%;
    }
    .spinner {
      width: 40px;
      height: 40px;
      border: 3px solid #f3f3f3;
      border-top: 3px solid #f59e0b;
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
      margin: 0 auto 1rem;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body>

<!-- Header -->
<header class="bg-[#1a1a1a] px-6 py-4 flex items-center justify-between sticky top-0 z-10">
  <div class="flex items-center gap-2">
    <span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span>
    <span class="text-white text-lg font-medium tracking-wide">The Spot</span>
  </div>
  <a href="cart.php"
     class="flex items-center gap-1 text-gray-400 text-sm hover:text-white transition">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
    </svg>
    Cart
  </a>
</header>

<main class="max-w-2xl mx-auto px-4 py-6 pb-32">

  <!-- Order summary -->
  <p class="text-xs font-medium uppercase tracking-widest text-gray-400 mb-4">Order Summary</p>
  <div class="bg-white rounded-xl border border-gray-100 px-5 py-4 mb-6">
    <?php foreach ($items as $item): ?>
    <div class="flex justify-between items-center py-2 border-b border-gray-50 last:border-0">
      <div>
        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($item['name']) ?></p>
        <p class="text-xs text-gray-400"><?= $cart[$item['id']] ?> × ₦<?= number_format($item['price'], 2) ?></p>
      </div>
      <p class="text-sm font-medium text-amber-500">
        ₦<?= number_format($item['price'] * $cart[$item['id']], 2) ?>
      </p>
    </div>
    <?php endforeach; ?>
    <div class="flex justify-between items-center pt-3 mt-1">
      <span class="text-sm font-medium text-gray-900">Total</span>
      <span class="text-base font-medium text-amber-500">₦<?= number_format($grandTotal, 2) ?></span>
    </div>
  </div>

  <!-- Delivery details form -->
  <p class="text-xs font-medium uppercase tracking-widest text-gray-400 mb-4">Your Details</p>
  <form method="POST" action="place_order.php" id="checkoutForm">
    <div class="flex flex-col gap-4">

      <div>
        <label class="text-xs text-gray-500 mb-1.5 block">Full Name</label>
        <input type="text" name="customer_name" placeholder="e.g. Simeon Minty" required>
      </div>

      <div>
        <label class="text-xs text-gray-500 mb-1.5 block">Phone Number</label>
        <input type="tel" name="customer_phone" placeholder="e.g. 08012345678" required>
      </div>

      <div>
        <label class="text-xs text-gray-500 mb-1.5 block">Delivery Address <span class="text-gray-300">(optional)</span></label>
        <textarea name="customer_address" placeholder="Enter your delivery address or leave blank for pickup"></textarea>
      </div>

    </div>

    <input type="hidden" name="source" value="online">
    <input type="hidden" name="fake_payment" value="0" id="paidInput">

    <!-- Pay button -->
    <button type="button" id="payBtn"
      class="mt-6 w-full bg-[#1a1a1a] text-white text-sm font-medium py-3.5 rounded-full hover:bg-amber-400 hover:text-[#1a1a1a] transition">
      Pay ₦<?= number_format($grandTotal, 2) ?>
    </button>
  </form>

</main>

<!-- Payment overlay -->
<div id="paymentOverlay">
  <div class="payment-box">
    <div class="spinner"></div>
    <p class="text-sm font-medium text-gray-900 mb-1">Processing payment...</p>
    <p class="text-xs text-gray-400">Please don't close this page</p>
  </div>
</div>

<!-- Toast -->
<div class="toast" id="toast">Payment Successful!</div>

<script>
const payBtn = document.getElementById('payBtn');
const overlay = document.getElementById('paymentOverlay');
const paidInput = document.getElementById('paidInput');
const toast = document.getElementById('toast');
const form = document.getElementById('checkoutForm');

payBtn.addEventListener('click', () => {
  // Basic validation
  const name = form.customer_name.value.trim();
  const phone = form.customer_phone.value.trim();
  if (!name || !phone) {
    form.reportValidity();
    return;
  }

  overlay.style.display = 'flex';
  payBtn.disabled = true;

  setTimeout(() => {
    toast.classList.add('show');
    setTimeout(() => {
      paidInput.value = 1;
      form.submit();
    }, 1200);
  }, 1800);
});
</script>

</body>
</html>
