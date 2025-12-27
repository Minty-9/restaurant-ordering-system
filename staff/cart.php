<?php
require "auth.php";
require __DIR__ . "/../src/database.php";

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
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="assets/css/staff.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cart</title>
</head>
<body>

<header class="staff-header">
    <h1>Cart</h1>
    <a href="menu.php" class="cart-back">← Back</a>
</header>

<div class="cart-container">
<?php foreach ($items as $i): 
    $qty = $cart[$i['id']];
    $subtotal = $i['price'] * $qty;
    $total += $subtotal;
?>
<div class="cart-item">
    <div class="cart-item-info">
        <strong><?= htmlspecialchars($i['name']) ?></strong>
        <span><?= $qty ?> × ₦<?= number_format($i['price'],2) ?> = ₦<?= number_format($subtotal,2) ?></span>
    </div>
    <a href="?remove=<?= $i['id'] ?>" class="cart-remove">Remove</a>
</div>
<?php endforeach; ?>

<div class="cart-summary">
    <p><strong>Total: ₦<?= number_format($total,2) ?></strong></p>
    <a href="place_order.php" class="btn-primary btn-fullwidth">Place Order</a>
</div>
</div>

<script>
const urlParams = new URLSearchParams(window.location.search);
if(urlParams.get('removed') === '1') {
    alert('Item removed from cart!');
}
</script>

</body>
</html>
