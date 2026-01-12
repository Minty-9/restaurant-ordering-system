<?php
require __DIR__ . "/includes/auth.php";
require __DIR__ . "/../../src/database.php";

// CREATE
if (isset($_POST['create'])) {
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $cat = $_POST['category'];

    $stmt = $pdo->prepare("INSERT INTO menu_items (name, price, category_id) VALUES (?, ?, ?)");
    $stmt->execute([$name, $price, $cat]);

    header("Location: index.php?page=items");
    exit;
}

// DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $pdo->prepare("DELETE FROM menu_items WHERE id=?")->execute([$id]);

    header("Location: index.php?page=items");
    exit;
}

// LIST ITEMS
$items = $pdo->query("
    SELECT menu_items.*, categories.name AS cat_name 
    FROM menu_items 
    LEFT JOIN categories ON categories.id = menu_items.category_id
    ORDER BY menu_items.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

// CATEGORIES FOR SELECT
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content">

    <h1 class="page-title">Manage Items</h1>

    <div class="card">
        <h2>Add New Item</h2>

        <form method="POST" class="form-grid">
            <input type="text" name="name" placeholder="Item name" required>
            <input type="number" step="0.01" name="price" placeholder="Price" required>

            <select name="category" required>
                <option value="">Select category</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" name="create" class="btn-primary">Add Item</button>
        </form>
    </div>

    <div class="card">
        <h2>Items List</h2>

        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Category</th>
                    <th style="width:120px;">Actions</th>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($items as $i): ?>
                <tr>
                    <td><?= htmlspecialchars($i['name']) ?></td>
                    <td><?= number_format($i['price'], 2) ?></td>
                    <td><?= htmlspecialchars($i['cat_name']) ?></td>

                    <td>
                        <a href="index.php?page=items&delete=<?= $i['id'] ?>" onclick="return confirm('Delete item?')" class="btn-danger-small">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>

        </table>
    </div>

</div>
