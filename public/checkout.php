<?php
session_start();
require __DIR__ . "/../src/database.php";

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    header("Location: cart.php");
    exit;
}

/* Fetch cart items */
$itemIds = array_keys($cart);
$placeholders = implode(',', array_fill(0, count($itemIds), '?'));

$stmt = $pdo->prepare("
    SELECT id, name, price
    FROM menu_items
    WHERE id IN ($placeholders)
");
$stmt->execute($itemIds);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Calculate total */
$grandTotal = 0;
foreach ($items as $item) {
    $grandTotal += $item['price'] * $cart[$item['id']];
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Checkout</title>
<link rel="stylesheet" href="assets/css/style.css">

</head>
<body>

<h1 style="text-align:center; margin-top:1rem;">Checkout</h1>

<form class="checkout-form" method="POST" action="place_order.php">
    <label>
        Full Name
        <input type="text" name="customer_name" required>
    </label>

    <label>
        Phone Number
        <input type="text" name="customer_phone" required>
    </label>

    <label>
        Address
        <textarea name="customer_address"></textarea>
    </label>

    <input type="hidden" name="source" value="online">
    <input type="hidden" name="fake_payment" value="0" id="paidInput">

    <button type="button" id="payBtn" class="pay-btn">
        Pay ₦<?= number_format($grandTotal, 2) ?>
    </button>
</form>

<div id="paymentOverlay" class="payment-overlay" style="display:none">
    <div class="payment-box">
        Processing payment...
    </div>
</div>

<div id="toast" class="toast">Payment Successful!</div>

<script>
const payBtn = document.getElementById('payBtn');
const overlay = document.getElementById('paymentOverlay');
const paidInput = document.getElementById('paidInput');
const toast = document.getElementById('toast');

payBtn.addEventListener('click', () => {
    overlay.style.display = 'flex';
    payBtn.disabled = true;

    setTimeout(() => {
        paidInput.value = 1;
        document.querySelector('form').submit();
        showToast();
    }, 1800);
});

function showToast() {
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2500);
}
</script>

</body>
</html>
