<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>The Spot — Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { background: #111; font-family: sans-serif; display: flex; flex-direction: column; min-height: 100vh; }

    /* Layout */
    .app-shell { display: flex; flex: 1; }
    .sidebar {
      width: 220px;
      background: #1a1a1a;
      border-right: 0.5px solid #2a2a2a;
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 57px;
      left: 0;
      bottom: 0;
      overflow-y: auto;
      z-index: 5;
    }
    .main { margin-left: 220px; flex: 1; padding: 2rem; min-height: calc(100vh - 57px); }

    /* Nav links */
    .nav-link {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 20px;
      font-size: 13px;
      color: #888;
      text-decoration: none;
      border-left: 2px solid transparent;
      transition: all 0.15s;
    }
    .nav-link:hover { color: #fff; background: #222; }
    .nav-link.active { color: #f59e0b; border-left-color: #f59e0b; background: #f59e0b0d; }

    /* Cards */
    .card {
      background: #1e1e1e;
      border: 0.5px solid #2e2e2e;
      border-radius: 16px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
    }
    .card h2 { font-size: 14px; font-weight: 500; color: #fff; margin-bottom: 1rem; }

    /* Stat cards */
    .stat-card {
      background: #1e1e1e;
      border: 0.5px solid #2e2e2e;
      border-radius: 14px;
      padding: 1.25rem 1.5rem;
    }
    .stat-label { font-size: 11px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
    .stat-value { font-size: 28px; font-weight: 500; color: #fff; }

    /* Page title */
    .page-title { font-size: 18px; font-weight: 500; color: #fff; margin-bottom: 1.5rem; }

    /* Tables */
    .data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .data-table th { text-align: left; padding: 10px 12px; color: #555; font-weight: 500; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 0.5px solid #2a2a2a; }
    .data-table td { padding: 12px 12px; color: #ccc; border-bottom: 0.5px solid #1e1e1e; vertical-align: middle; }
    .data-table tr:last-child td { border-bottom: none; }
    .data-table tr:hover td { background: #1a1a1a; }

    /* Forms */
    .form-row { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; }
    input[type="text"], input[type="number"], select, textarea {
      background: #2a2a2a;
      border: 0.5px solid #3a3a3a;
      border-radius: 10px;
      padding: 10px 14px;
      font-size: 13px;
      color: #fff;
      outline: none;
      transition: border-color 0.2s;
      font-family: sans-serif;
    }
    input:focus, select:focus, textarea:focus { border-color: #f59e0b; }
    input::placeholder, textarea::placeholder { color: #555; }
    select option { background: #2a2a2a; }

    /* Buttons */
    .btn-primary {
      background: #f59e0b;
      color: #1a1a1a;
      border: none;
      border-radius: 8px;
      padding: 10px 18px;
      font-size: 13px;
      font-weight: 500;
      cursor: pointer;
      transition: background 0.2s;
      text-decoration: none;
      display: inline-block;
    }
    .btn-primary:hover { background: #fbbf24; }

    .btn-secondary {
      background: transparent;
      color: #888;
      border: 0.5px solid #3a3a3a;
      border-radius: 8px;
      padding: 9px 16px;
      font-size: 13px;
      cursor: pointer;
      transition: all 0.2s;
      text-decoration: none;
      display: inline-block;
    }
    .btn-secondary:hover { color: #fff; border-color: #555; }

    .btn-danger {
      background: #ef444420;
      color: #f87171;
      border: 0.5px solid #ef444430;
      border-radius: 8px;
      padding: 6px 12px;
      font-size: 12px;
      cursor: pointer;
      transition: all 0.2s;
      text-decoration: none;
      display: inline-block;
    }
    .btn-danger:hover { background: #ef444440; }

    .btn-view {
      background: #f59e0b15;
      color: #f59e0b;
      border: 0.5px solid #f59e0b30;
      border-radius: 8px;
      padding: 6px 12px;
      font-size: 12px;
      font-weight: 500;
      text-decoration: none;
      display: inline-block;
      transition: all 0.2s;
    }
    .btn-view:hover { background: #f59e0b25; }

    /* Badges */
    .badge {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .badge-new        { background: #f59e0b22; color: #f59e0b; }
    .badge-preparing  { background: #3b82f622; color: #60a5fa; }
    .badge-ready      { background: #22c55e22; color: #4ade80; }
    .badge-completed  { background: #8b5cf622; color: #a78bfa; }
    .badge-cancelled  { background: #ef444422; color: #f87171; }
    .badge-online     { background: #3b82f622; color: #60a5fa; }
    .badge-walkin     { background: #f59e0b22; color: #f59e0b; }
    .badge-paid       { background: #22c55e22; color: #4ade80; }
    .badge-unpaid     { background: #ef444422; color: #f87171; }

    /* Search bar */
    .search-row { display: flex; gap: 8px; margin-bottom: 1.5rem; }
    .search-row input { flex: 1; }

    /* Pagination */
    .pagination { display: flex; align-items: center; gap: 12px; margin-top: 1.5rem; font-size: 13px; }
    .pagination a { color: #f59e0b; text-decoration: none; }
    .pagination span { color: #555; }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #2a2a2a; border-radius: 4px; }
  </style>
</head>
<body>

<!-- Top bar -->
<header style="background:#1a1a1a; border-bottom:0.5px solid #2a2a2a; padding:0 1.5rem; height:57px; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:10;">
  <div style="display:flex; align-items:center; gap:8px;">
    <span style="width:10px; height:10px; border-radius:50%; background:#f59e0b; display:inline-block;"></span>
    <span style="color:#fff; font-size:15px; font-weight:500; letter-spacing:0.3px;">The Spot</span>
    <span style="color:#444; font-size:13px; margin-left:4px;">/ Admin</span>
  </div>
  <div style="display:flex; align-items:center; gap:16px;">
    <span style="color:#555; font-size:12px;"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
    <a href="/admin/logout.php" style="color:#555; font-size:12px; text-decoration:none; transition:color 0.2s;" onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='#555'">Logout</a>
  </div>
</header>

<div class="app-shell">

<!-- Sidebar -->
<aside class="sidebar">
  <nav style="padding: 1rem 0;">
    <?php $currentPage = $_GET['page'] ?? 'dashboard'; ?>

    <a href="index.php?page=dashboard"
       class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" style="width:15px;height:15px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
      </svg>
      Dashboard
    </a>

    <a href="index.php?page=orders"
       class="nav-link <?= $currentPage === 'orders' || $currentPage === 'order_view' ? 'active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" style="width:15px;height:15px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
      </svg>
      Orders
    </a>

    <a href="index.php?page=items"
       class="nav-link <?= $currentPage === 'items' ? 'active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" style="width:15px;height:15px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
      </svg>
      Menu Items
    </a>

    <a href="index.php?page=categories"
       class="nav-link <?= $currentPage === 'categories' ? 'active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" style="width:15px;height:15px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-5 5a2 2 0 01-2.828 0l-7-7A2 2 0 013 9V4a1 1 0 011-1z"/>
      </svg>
      Categories
    </a>
  </nav>

  <!-- Sidebar footer -->
  <div style="margin-top:auto; padding:1rem 1.25rem; border-top:0.5px solid #2a2a2a;">
    <p style="font-size:11px; color:#444;">The Spot &copy; <?= date('Y') ?></p>
  </div>
</aside>

<!-- Main content starts -->
<main class="main">
