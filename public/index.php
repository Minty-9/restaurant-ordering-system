<?php
require_once __DIR__ . '/../src/bootstrap_sqlite.php';

session_start();
require __DIR__ . "/../src/database.php";

$dbFile = sys_get_temp_dir() . '/restaurant.sqlite';
if (!file_exists($dbFile)) {
    require __DIR__ . '/../database/seed.php';
}


$_SESSION['cart'] ??= [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['item_id'];
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
    header("Location: index.php");
    exit;
}

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
    $menu = $pdo->query("
        SELECT m.id, m.name, m.price, c.name AS category
        FROM menu_items m
        JOIN categories c ON c.id = m.category_id
        ORDER BY c.name, m.name
    ")->fetchAll(PDO::FETCH_ASSOC);

    $grouped = [];
    foreach ($menu as $i) {
        $grouped[$i['category']][] = $i;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="assets/css/style.css">
<title>Menu</title>
</head>
<body>

<header class="menu-header">
    <h1>Menu</h1>
    <a href="cart.php">Cart (<?= array_sum($_SESSION['cart']) ?>)</a>
</header>

<form class="search-bar">
    <input type="text" name="q" placeholder="Search food or category"
           value="<?= htmlspecialchars($search) ?>">
    <button>Search</button>
</form>

<main class="menu">
<?php if ($isSearch): ?>
    <h2>Results</h2>

    <?php if (!$items): ?>
        <p>No results found.</p>
    <?php endif; ?>

    <?php foreach ($items as $i): ?>
        <div class="menu-item">
            <div>
                <strong><?= htmlspecialchars($i['name']) ?></strong>
                <span><?= htmlspecialchars($i['category']) ?></span>
                <span>₦<?= number_format($i['price'], 2) ?></span>
            </div>
            <form method="POST">
                <input type="hidden" name="item_id" value="<?= $i['id'] ?>">
                <button type="submit" class="add-to-cart">Add</button>
            </form>
        </div>
    <?php endforeach; ?>

<?php else: ?>
    <?php foreach ($grouped as $cat => $items): ?>
        <section class="category">
            <h2><?= htmlspecialchars($cat) ?></h2>
            <?php foreach ($items as $i): ?>
                <div class="menu-item">
                    <div>
                        <strong><?= htmlspecialchars($i['name']) ?></strong>
                        <span>₦<?= number_format($i['price'], 2) ?></span>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="item_id" value="<?= $i['id'] ?>">
                        <button type="submit" class="add-to-cart">Add</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endforeach; ?>
<?php endif; ?>
</main>

<div id="toast" class="toast">Added to cart</div>

<script>
const toast = document.getElementById('toast');

document.querySelectorAll('form[method="POST"]').forEach(form => {
    form.addEventListener('submit', function (e) {
        e.preventDefault(); // stop instant reload

        toast.textContent = 'Added to cart';
        toast.classList.add('show');

        // allow user to see toast, then submit
        setTimeout(() => {
            form.submit();
        }, 1000);
    });
});
</script>




</body>
</html>
