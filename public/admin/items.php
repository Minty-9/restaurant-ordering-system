<?php
require __DIR__ . "/includes/auth.php";
require __DIR__ . "/../../src/database.php";

if (isset($_POST['create'])) {
    $name  = trim($_POST['name']);
    $price = trim($_POST['price']);
    $cat   = $_POST['category'];
    $pdo->prepare("INSERT INTO menu_items (name, price, category_id) VALUES (?, ?, ?)")->execute([$name, $price, $cat]);
    header("Location: index.php?page=items&success=1");
    exit;
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM menu_items WHERE id=?")->execute([(int)$_GET['delete']]);
    header("Location: index.php?page=items");
    exit;
}

$items = $pdo->query("
    SELECT menu_items.*, categories.name AS cat_name
    FROM menu_items
    LEFT JOIN categories ON categories.id = menu_items.category_id
    ORDER BY menu_items.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<h1 class="page-title">Menu Items</h1>

<?php if (isset($_GET['success'])): ?>
<div style="background:#22c55e15; border:0.5px solid #22c55e30; color:#4ade80; font-size:13px; border-radius:10px; padding:10px 16px; margin-bottom:1.5rem;">
  Item added successfully.
</div>
<?php endif; ?>

<!-- Add item form -->
<div class="card">
  <h2>Add New Item</h2>
  <form method="POST">
    <div class="form-row">
      <input type="text" name="name" placeholder="Item name" required style="flex:2; min-width:160px;">
      <input type="number" step="0.01" name="price" placeholder="Price (₦)" required style="flex:1; min-width:120px;">
      <select name="category" required style="flex:1; min-width:150px;">
        <option value="">Select category</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" name="create" class="btn-primary">Add Item</button>
    </div>
  </form>
</div>

<!-- Items list -->
<div class="card" style="padding:0; overflow:hidden;">
  <div style="padding:1.25rem 1.5rem; border-bottom:0.5px solid #2a2a2a;">
    <h2 style="margin-bottom:0;">All Items <span style="color:#555; font-weight:400;">(<?= count($items) ?>)</span></h2>
  </div>
  <?php if (!$items): ?>
    <p style="color:#555; font-size:13px; padding:1.5rem;">No items yet. Add one above.</p>
  <?php else: ?>
  <table class="data-table">
    <thead>
      <tr><th>Name</th><th>Category</th><th>Price</th><th style="width:100px;"></th></tr>
    </thead>
    <tbody>
      <?php foreach ($items as $i): ?>
      <tr>
        <td style="color:#fff;"><?= htmlspecialchars($i['name']) ?></td>
        <td><?= htmlspecialchars($i['cat_name'] ?? '—') ?></td>
        <td style="color:#f59e0b;">₦<?= number_format($i['price'], 2) ?></td>
        <td>
          <a href="index.php?page=items&delete=<?= $i['id'] ?>"
             onclick="return confirm('Delete <?= htmlspecialchars(addslashes($i['name'])) ?>?')"
             class="btn-danger">Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
