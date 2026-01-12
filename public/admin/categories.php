<?php
require __DIR__ . "/includes/auth.php";
require __DIR__ . "/../../src/database.php";

// CREATE
if (isset($_POST['create'])) {
    $name = $_POST['name'];

    $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->execute([$name]);

    header("Location: index.php?page=categories");
    exit;
}

// DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([$id]);

    header("Location: index.php?page=categories");
    exit;
}

$cats = $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content">

    <h1 class="page-title">Manage Categories</h1>

    <div class="card">
        <h2>Add Category</h2>

        <form method="POST" class="form-grid">
            <input type="text" name="name" placeholder="Category name" required>
            <button type="submit" name="create" class="btn-primary">Add</button>
        </form>
    </div>

    <div class="card">
        <h2>Category List</h2>

        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th style="width:120px;">Actions</th>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($cats as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['name']) ?></td>

                    <td>
                        <a href="index.php?page=categories&delete=<?= $c['id'] ?>" onclick="return confirm('Delete category?')" class="btn-danger-small">Delete</a>
                    </td>

                </tr>
            <?php endforeach; ?>
            </tbody>

        </table>
    </div>

</div>
