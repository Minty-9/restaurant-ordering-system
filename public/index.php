<?php
require_once __DIR__ . '/../src/bootstrap_sqlite.php';
session_start();
require __DIR__ . "/../src/database.php";
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
$cartCount = array_sum($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>The Spot — Menu</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { background: #f5f5f4; font-family: sans-serif; }

    .toast {
      position: fixed;
      bottom: 24px;
      left: 50%;
      transform: translateX(-50%) translateY(80px);
      background: #1a1a1a;
      color: #fff;
      padding: 10px 24px;
      border-radius: 999px;
      font-size: 14px;
      transition: transform 0.3s ease;
      z-index: 99;
    }
    .toast.show {
      transform: translateX(-50%) translateY(0);
    }

    .add-btn:hover {
      background: #f59e0b !important;
      color: #1a1a1a !important;
    }

    ::-webkit-scrollbar { display: none; }
  </style>
</head>
<body>

<!-- Header -->
<header class="bg-[#1a1a1a] px-6 py-4 flex items-center justify-between sticky top-0 z-10">
  <div class="flex items-center gap-2">
    <span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span>
    <span class="text-white text-lg font-medium tracking-wide">The Spot</span>
  </div>
  <a href="cart.php"
     class="flex items-center gap-2 bg-amber-400 text-[#1a1a1a] text-sm font-medium px-4 py-2 rounded-full hover:bg-amber-300 transition">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 6h13M7 13L5.4 5M10 21a1 1 0 100-2 1 1 0 000 2zm7 0a1 1 0 100-2 1 1 0 000 2z"/>
    </svg>
    Cart (<?= $cartCount ?>)
  </a>
</header>

<!-- Search -->
<div class="bg-[#1a1a1a] px-6 pb-5">
  <form method="GET" action="index.php">
    <div class="flex items-center gap-3 bg-[#2a2a2a] rounded-xl px-4 py-3">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
      </svg>
      <input
        type="text"
        name="q"
        placeholder="Search food or category..."
        value="<?= htmlspecialchars($search) ?>"
        class="bg-transparent text-white text-sm outline-none flex-1 placeholder-gray-500"
      >
      <?php if ($isSearch): ?>
        <a href="index.php" class="text-gray-400 text-xs hover:text-white transition">Clear</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- Category Tabs (only show when not searching) -->
<?php if (!$isSearch): ?>
<div class="bg-white border-b border-gray-100 px-6 py-3 flex gap-2 overflow-x-auto" id="cat-tabs">
  <button onclick="filterCat(this, 'all')"
    class="cat-tab active-tab text-sm font-medium px-4 py-1.5 rounded-full border border-gray-200 whitespace-nowrap transition">
    All
  </button>
  <?php foreach (array_keys($grouped) as $cat): ?>
  <button onclick="filterCat(this, '<?= htmlspecialchars($cat) ?>')"
    class="cat-tab text-sm font-medium px-4 py-1.5 rounded-full border border-gray-200 whitespace-nowrap text-gray-500 transition">
    <?= htmlspecialchars($cat) ?>
  </button>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Menu -->
<main class="max-w-2xl mx-auto px-4 py-4 pb-24">

  <?php if ($isSearch): ?>
    <p class="text-xs font-medium uppercase tracking-widest text-gray-400 mb-4">
      Results for "<?= htmlspecialchars($search) ?>"
    </p>
    <?php if (!$items): ?>
      <div class="text-center py-16">
        <p class="text-gray-400 text-sm">No results found. Try a different keyword.</p>
        <a href="index.php" class="text-amber-500 text-sm mt-2 inline-block hover:underline">Back to menu</a>
      </div>
    <?php endif; ?>
    <?php foreach ($items as $i): ?>
      <div class="bg-white rounded-xl border border-gray-100 px-5 py-4 flex items-center justify-between mb-3">
        <div>
          <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($i['name']) ?></p>
          <p class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($i['category']) ?></p>
          <p class="text-sm font-medium text-amber-500 mt-1">₦<?= number_format($i['price'], 2) ?></p>
        </div>
        <form method="POST">
          <input type="hidden" name="item_id" value="<?= $i['id'] ?>">
          <button type="submit"
            class="add-btn w-9 h-9 rounded-full bg-[#1a1a1a] text-white text-xl flex items-center justify-center transition ml-4">
            +
          </button>
        </form>
      </div>
    <?php endforeach; ?>

  <?php else: ?>
    <?php foreach ($grouped as $cat => $items): ?>
      <div class="menu-section" data-cat="<?= htmlspecialchars($cat) ?>">
        <p class="text-xs font-medium uppercase tracking-widest text-gray-400 mt-6 mb-3">
          <?= htmlspecialchars($cat) ?>
        </p>
        <?php foreach ($items as $i): ?>
          <div class="bg-white rounded-xl border border-gray-100 px-5 py-4 flex items-center justify-between mb-3">
            <div>
              <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($i['name']) ?></p>
              <p class="text-sm font-medium text-amber-500 mt-1">₦<?= number_format($i['price'], 2) ?></p>
            </div>
            <form method="POST">
              <input type="hidden" name="item_id" value="<?= $i['id'] ?>">
              <button type="submit"
                class="add-btn w-9 h-9 rounded-full bg-[#1a1a1a] text-white text-xl flex items-center justify-center transition ml-4">
                +
              </button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

</main>

<!-- Toast -->
<div class="toast" id="toast">Added to cart</div>

<script>
  // Toast on add
  document.querySelectorAll('form[method="POST"]').forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const toast = document.getElementById('toast');
      toast.classList.add('show');
      setTimeout(() => { toast.classList.remove('show'); }, 1000);
      setTimeout(() => { form.submit(); }, 1000);
    });
  });

  // Category filter tabs
  function filterCat(el, cat) {
    document.querySelectorAll('.cat-tab').forEach(t => {
      t.classList.remove('active-tab', 'bg-[#1a1a1a]', 'text-white', 'border-[#1a1a1a]');
      t.classList.add('text-gray-500', 'border-gray-200');
    });
    el.classList.add('active-tab', 'bg-[#1a1a1a]', 'text-white', 'border-[#1a1a1a]');
    el.classList.remove('text-gray-500', 'border-gray-200');

    document.querySelectorAll('.menu-section').forEach(section => {
      if (cat === 'all' || section.dataset.cat === cat) {
        section.style.display = '';
      } else {
        section.style.display = 'none';
      }
    });
  }

  // Set initial active tab style
  document.querySelector('.active-tab')?.classList.add('bg-[#1a1a1a]', 'text-white', 'border-[#1a1a1a]');
</script>

</body>
</html>
