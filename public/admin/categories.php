<?php
require __DIR__ . "/includes/auth.php";
require __DIR__ . "/../../src/database.php";

if (isset($_POST['create'])) {
    $name = trim($_POST['name']);
    $pdo->prepare("INSERT INTO categories (name) VALUES (?)")->execute([$name]);
    header("Location: index.php?page=categories&success=1");
    exit;
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([(int)$_GET['delete']]);
    header("Location: index.php?page=categories");
    exit;
}

$cats = $pdo->query("
    SELECT c.*, COUNT(m.id) AS item_count
    FROM categories c
    LEFT JOIN menu_items m ON m.category_id = c.id
    GROUP BY c.id
    ORDER BY c.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<h1 class="page-title">Categories</h1>

<?php if (isset($_GET['success'])): ?>
<div style="background:#22c55e15; border:0.5px solid #22c55e30; color:#4ade80; font-size:13px; border-radius:10px; padding:10px 16px; margin-bottom:1.5rem;">
  Category added successfully.
</div>
<?php endif; ?>

<!-- Add category -->
<div class="card">
  <h2>Add New Category</h2>
  <form method="POST">
    <div class="form-row">
      <input type="text" name="name" placeholder="Category name" required style="flex:1; min-width:200px;">
      <button type="submit" name="create" class="btn-primary">Add Category</button>
    </div>
  </form>
</div>

<!-- Category list -->
<div class="card" style="padding:0; overflow:hidden;">
  <div style="padding:1.25rem 1.5rem; border-bottom:0.5px solid #2a2a2a;">
    <h2 style="margin-bottom:0;">All Categories <span style="color:#555; font-weight:400;">(<?= count($cats) ?>)</span></h2>
  </div>
  <?php if (!$cats): ?>
    <p style="color:#555; font-size:13px; padding:1.5rem;">No categories yet. Add one above.</p>
  <?php else: ?>
  <table class="data-table">
    <thead>
      <tr><th>Name</th><th>Items</th><th style="width:100px;"></th></tr>
    </thead>
    <tbody>
      <?php foreach ($cats as $c): ?>
      <tr>
        <td style="color:#fff;"><?= htmlspecialchars($c['name']) ?></td>
        <td>
          <span style="background:#f59e0b15; color:#f59e0b; font-size:11px; padding:3px 10px; border-radius:999px;">
            <?= $c['item_count'] ?> item<?= $c['item_count'] != 1 ? 's' : '' ?>
          </span>
        </td>
        <td>
          <a href="index.php?page=categories&delete=<?= $c['id'] ?>"
             onclick="return confirm('Delete <?= htmlspecialchars(addslashes($c['name'])) ?>? This may affect linked items.')"
             class="btn-danger">Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
