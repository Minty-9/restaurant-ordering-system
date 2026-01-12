<?php
require __DIR__ . "/includes/auth.php";
require __DIR__ . "/../../src/database.php";

/* ===== Stats ===== */
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalItems      = $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();
$totalOrders     = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

/* ===== Search ===== */
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
    $categories = $pdo->query("
        SELECT c.id, c.name
        FROM categories c
        ORDER BY c.name
    ")->fetchAll(PDO::FETCH_ASSOC);

    $itemsByCategory = [];
    foreach ($categories as $c) {
        $stmt = $pdo->prepare("
            SELECT id, name, price
            FROM menu_items
            WHERE category_id = ?
            ORDER BY name
        ");
        $stmt->execute([$c['id']]);
        $itemsByCategory[$c['name']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<div class="main-content">
    <h1 class="page-title">Dashboard</h1>

    <div class="card-stats">
        <div class="card"><h3>Categories</h3><p><?= $totalCategories ?></p></div>
        <div class="card"><h3>Items</h3><p><?= $totalItems ?></p></div>
        <div class="card"><h3>Orders</h3><p><?= $totalOrders ?></p></div>
    </div>

    <form method="GET" class="search-bar">
        <input type="hidden" name="page" value="dashboard">
        <input type="text" name="q" placeholder="Search items or categories"
               value="<?= htmlspecialchars($search) ?>">
        <button>Search</button>
    </form>

    <div class="card">
        <?php if ($isSearch): ?>
            <h2>Search results for “<?= htmlspecialchars($search) ?>”</h2>

            <?php if (!$items): ?>
                <p>No results found.</p>
            <?php else: ?>
                <table class="table">
                    <tr><th>Name</th><th>Category</th><th>Price</th></tr>
                    <?php foreach ($items as $i): ?>
                        <tr>
                            <td><?= htmlspecialchars($i['name']) ?></td>
                            <td><?= htmlspecialchars($i['category']) ?></td>
                            <td>₦<?= number_format($i['price'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>

        <?php else: ?>
            <h2>Items by Category</h2>

            <?php foreach ($itemsByCategory as $cat => $items): ?>
                <h3><?= htmlspecialchars($cat) ?></h3>

                <?php if (!$items): ?>
                    <p>No items.</p>
                <?php else: ?>
                    <table class="table">
                        <tr><th>Name</th><th>Price</th></tr>
                        <?php foreach ($items as $i): ?>
                            <tr>
                                <td><?= htmlspecialchars($i['name']) ?></td>
                                <td>₦<?= number_format($i['price'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            <?php endforeach; ?>

        <?php endif; ?>
    </div>
</div>
