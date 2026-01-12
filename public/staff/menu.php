<?php
require "auth.php";
require __DIR__ . "/../../src/database.php";

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

/* Table number set */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_table'])) {
    $_SESSION['table_number'] = trim($_POST['table_number']);
    header("Location: menu.php");
    exit;
}

/* Add item to cart */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    if (!isset($_SESSION['table_number'])) {
        header("Location: menu.php");
        exit;
    }

    $id = (int)$_POST['item_id'];
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;

    header("Location: menu.php?added=1");
    exit;
}

/* Fetch menu grouped by category */
$itemsRaw = $pdo->query("
    SELECT m.id, m.name, m.price, c.name AS category
    FROM menu_items m
    JOIN categories c ON c.id = m.category_id
    ORDER BY c.name, m.name
")->fetchAll(PDO::FETCH_ASSOC);

// Group items by category
$itemsByCategory = [];
foreach ($itemsRaw as $item) {
    $itemsByCategory[$item['category']][] = $item;
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="assets/css/staff.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Walk-in Menu</title>
</head>
<body>

<header class="staff-header">
    <h1>New Walk-in Order</h1>
    <?php if (isset($_SESSION['table_number'])): ?>
        <div class="staff-header-info">
            <span>Table <?= htmlspecialchars($_SESSION['table_number']) ?></span>
            <a href="cart.php" class="cart-btn">Cart (<?= array_sum($_SESSION['cart']) ?>)</a>
        </div>
    <?php endif; ?>
</header>

<?php if (!isset($_SESSION['table_number'])): ?>
<div class="table-select-card">
    <h2>Select Table</h2>
    <form method="POST">
        <input type="text" name="table_number" placeholder="Table number" required>
        <button name="set_table" class="btn-primary">Continue</button>
    </form>
</div>
<?php else: ?>

<?php foreach ($itemsByCategory as $category => $items): ?>
<div class="category-section">
    <h2 class="category-title"><?= htmlspecialchars($category) ?></h2>
    <div class="menu-grid">
        <?php foreach ($items as $i): ?>
        <div class="menu-card">
            <h3><?= htmlspecialchars($i['name']) ?></h3>
            <span class="menu-price">₦<?= number_format($i['price'], 2) ?></span>
            <form method="POST" class="add-to-cart-form">
                <input type="hidden" name="item_id" value="<?= $i['id'] ?>">
                <button class="btn-add">Add</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<?php endif; ?>

<script>
function showToast(message, duration = 2000) {
    let toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => toast.classList.add('show'), 50);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => document.body.removeChild(toast), 400);
    }, duration);
}

const urlParams = new URLSearchParams(window.location.search);
if(urlParams.get('added') === '1') showToast('Item added to cart!');
</script>

</body>
</html>
