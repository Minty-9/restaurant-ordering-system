<?php
session_start();
require __DIR__ . "/../src/database.php";

/* Cart init */
$cart = $_SESSION['cart'] ?? [];
$items = [];

/* Remove item (AJAX-friendly) */
if (isset($_GET['remove'])) {
    $id = (int) $_GET['remove'];
    unset($_SESSION['cart'][$id]);
    exit; // important: no redirect for JS removal
}

/* Fetch items */
if (!empty($cart)) {
    $ids = implode(',', array_map('intval', array_keys($cart)));

    $stmt = $pdo->query("
        SELECT id, name, price
        FROM menu_items
        WHERE id IN ($ids)
    ");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* Total */
$grandTotal = 0;
foreach ($items as $item) {
    $grandTotal += $item['price'] * $cart[$item['id']];
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="assets/css/style.css">
<title>Your Cart</title>
</head>
<body>

<header class="menu-header">
    <h1>Your Cart</h1>
    <a href="index.php">← Menu</a>
</header>

<main class="cart-page">

<?php if (!$items): ?>
    <div class="empty-cart">
        <p>Your cart is empty.</p>
        <a href="index.php" class="btn-primary btn-block">Back to Menu</a>
    </div>

<?php else: ?>
    <div class="cart-list">
        <?php foreach ($items as $item): ?>
            <div class="cart-row">
                <div class="cart-info">
                    <strong><?= htmlspecialchars($item['name']) ?></strong>
                    <span>
                        <?= $cart[$item['id']] ?> × ₦<?= number_format($item['price'], 2) ?>
                    </span>
                </div>

                <button
                    class="btn-remove"
                    data-id="<?= $item['id'] ?>">
                    Remove
                </button>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="cart-summary">
        <h3>Total: ₦<?= number_format($grandTotal, 2) ?></h3>
        <a href="checkout.php" class="btn-primary btn-block">
            Proceed to Checkout
        </a>
    </div>
<?php endif; ?>

</main>

<!-- Toast + Remove UX -->
<script>
function showToast(message, duration = 2000) {
    const toast = document.createElement('div');
    toast.className = 'toast show';
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

document.querySelectorAll('.btn-remove').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;

        fetch(`cart.php?remove=${id}`)
            .then(() => {
                showToast('Item removed from cart');
                setTimeout(() => location.reload(), 600);
            });
    });
});
</script>

</body>
</html>
