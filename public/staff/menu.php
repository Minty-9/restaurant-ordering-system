<?php
require "auth.php";
require __DIR__ . "/../../src/database.php";
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_table'])) {
    $_SESSION['table_number'] = trim($_POST['table_number']);
    header("Location: menu.php");
    exit;
}

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

$itemsRaw = $pdo->query("
    SELECT m.id, m.name, m.price, c.name AS category
    FROM menu_items m
    JOIN categories c ON c.id = m.category_id
    ORDER BY c.name, m.name
")->fetchAll(PDO::FETCH_ASSOC);

$itemsByCategory = [];
foreach ($itemsRaw as $item) {
    $itemsByCategory[$item['category']][] = $item;
}

$cartCount = array_sum($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>The Spot — Walk-in Order</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { background: #111; font-family: sans-serif; }

    input[type="text"] {
      width: 100%;
      background: #2a2a2a;
      border: 0.5px solid #3a3a3a;
      border-radius: 12px;
      padding: 14px 16px;
      font-size: 15px;
      color: #fff;
      outline: none;
      transition: border-color 0.2s;
    }
    input[type="text"]:focus { border-color: #f59e0b; }
    input[type="text"]::placeholder { color: #555; }

    .menu-card {
      background: #1e1e1e;
      border: 0.5px solid #2e2e2e;
      border-radius: 14px;
      padding: 1rem;
      display: flex;
      flex-direction: column;
      gap: 8px;
      transition: border-color 0.2s;
    }
    .menu-card:hover { border-color: #f59e0b44; }

    .add-btn {
      width: 100%;
      padding: 8px;
      border-radius: 8px;
      background: #2a2a2a;
      color: #ccc;
      font-size: 13px;
      font-weight: 500;
      border: 0.5px solid #3a3a3a;
      cursor: pointer;
      transition: background 0.2s, color 0.2s;
    }
    .add-btn:hover { background: #f59e0b; color: #1a1a1a; border-color: #f59e0b; }

    .toast {
      position: fixed;
      bottom: 24px;
      left: 50%;
      transform: translateX(-50%) translateY(80px);
      background: #1e1e1e;
      border: 0.5px solid #2e2e2e;
      color: #fff;
      padding: 10px 24px;
      border-radius: 999px;
      font-size: 13px;
      transition: transform 0.3s ease;
      z-index: 99;
    }
    .toast.show { transform: translateX(-50%) translateY(0); }
  </style>
</head>
<body class="min-h-screen">

<!-- Header -->
<header class="bg-[#1a1a1a] border-b border-[#2a2a2a] px-6 py-4 flex items-center justify-between sticky top-0 z-10">
  <div class="flex items-center gap-3">
    <div class="flex items-center gap-2">
      <span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span>
      <span class="text-white text-base font-medium tracking-wide">The Spot</span>
    </div>
    <span class="text-gray-600 text-sm">/ Walk-in Order</span>
    <?php if (isset($_SESSION['table_number'])): ?>
    <span class="bg-amber-400/10 text-amber-400 text-xs font-medium px-3 py-1 rounded-full border border-amber-400/20">
      Table <?= htmlspecialchars($_SESSION['table_number']) ?>
    </span>
    <?php endif; ?>
  </div>

  <div class="flex items-center gap-4">
    <?php if (isset($_SESSION['table_number'])): ?>
    <!-- Change table -->
    <form method="POST" class="flex items-center gap-2">
      <button name="set_table" value="1" type="submit"
        onclick="this.form['table_number'].value = prompt('Enter new table number:') || ''; return this.form['table_number'].value !== '';"
        class="text-gray-500 text-xs hover:text-gray-300 transition">
        Change table
      </button>
      <input type="hidden" name="table_number" value="">
    </form>
    <!-- Cart -->
    <a href="cart.php"
       class="flex items-center gap-2 bg-amber-400 text-[#1a1a1a] text-sm font-medium px-4 py-2 rounded-full hover:bg-amber-300 transition">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 6h13M10 21a1 1 0 100-2 1 1 0 000 2zm7 0a1 1 0 100-2 1 1 0 000 2z"/>
      </svg>
      Cart (<?= $cartCount ?>)
    </a>
    <?php endif; ?>
    <a href="logout.php" class="text-gray-600 text-xs hover:text-red-400 transition">Logout</a>
  </div>
</header>

<!-- Table select screen -->
<?php if (!isset($_SESSION['table_number'])): ?>
<div class="min-h-[80vh] flex items-center justify-center px-4">
  <div class="w-full max-w-sm">
    <div class="bg-[#1e1e1e] border border-[#2e2e2e] rounded-2xl px-6 py-8">
      <p class="text-white text-base font-medium mb-1">New Walk-in Order</p>
      <p class="text-gray-500 text-sm mb-6">Enter the table number to get started</p>
      <form method="POST" class="flex flex-col gap-4">
        <input type="text" name="table_number" placeholder="e.g. 5 or A3" required>
        <button name="set_table" type="submit"
          class="w-full bg-amber-400 text-[#1a1a1a] text-sm font-medium py-3.5 rounded-full hover:bg-amber-300 transition">
          Continue to Menu
        </button>
      </form>
    </div>
  </div>
</div>

<!-- Menu -->
<?php else: ?>
<div class="max-w-5xl mx-auto px-4 py-6 pb-24">

  <!-- Category tabs -->
  <div class="flex gap-2 overflow-x-auto pb-2 mb-6" id="cat-tabs">
    <button onclick="filterCat(this, 'all')"
      class="cat-tab active-tab text-xs font-medium px-4 py-1.5 rounded-full border border-[#3a3a3a] bg-[#1e1e1e] text-white whitespace-nowrap transition">
      All
    </button>
    <?php foreach (array_keys($itemsByCategory) as $cat): ?>
    <button onclick="filterCat(this, '<?= htmlspecialchars($cat) ?>')"
      class="cat-tab text-xs font-medium px-4 py-1.5 rounded-full border border-[#2e2e2e] text-gray-500 whitespace-nowrap transition hover:text-white">
      <?= htmlspecialchars($cat) ?>
    </button>
    <?php endforeach; ?>
  </div>

  <!-- Items -->
  <?php foreach ($itemsByCategory as $category => $items): ?>
  <div class="menu-section mb-8" data-cat="<?= htmlspecialchars($category) ?>">
    <p class="text-xs font-medium uppercase tracking-widest text-gray-600 mb-3">
      <?= htmlspecialchars($category) ?>
    </p>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
      <?php foreach ($items as $i): ?>
      <div class="menu-card">
        <p class="text-white text-sm font-medium leading-snug"><?= htmlspecialchars($i['name']) ?></p>
        <p class="text-amber-400 text-sm font-medium">₦<?= number_format($i['price'], 2) ?></p>
        <form method="POST" class="add-form mt-1">
          <input type="hidden" name="item_id" value="<?= $i['id'] ?>">
          <button type="submit" class="add-btn">+ Add</button>
        </form>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>

</div>
<?php endif; ?>

<div class="toast" id="toast">Item added to cart</div>

<script>
// Show toast if item was just added
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('added') === '1') {
  const toast = document.getElementById('toast');
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 2000);
  // Clean URL
  history.replaceState(null, '', 'menu.php');
}

// Category filter
function filterCat(el, cat) {
  document.querySelectorAll('.cat-tab').forEach(t => {
    t.classList.remove('active-tab', 'bg-[#1e1e1e]', 'text-white', 'border-[#3a3a3a]');
    t.classList.add('text-gray-500', 'border-[#2e2e2e]');
  });
  el.classList.add('active-tab', 'bg-[#1e1e1e]', 'text-white', 'border-[#3a3a3a]');
  el.classList.remove('text-gray-500');

  document.querySelectorAll('.menu-section').forEach(section => {
    section.style.display = (cat === 'all' || section.dataset.cat === cat) ? '' : 'none';
  });
}
</script>

</body>
</html>
