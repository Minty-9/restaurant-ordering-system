<?php
session_start();
require __DIR__ . "/../../src/database.php";
if (!empty($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit;
}
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM admin_users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['username'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>The Spot — Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { background: #1a1a1a; font-family: sans-serif; }
    input[type="text"], input[type="password"] {
      width: 100%;
      background: #2a2a2a;
      border: 0.5px solid #3a3a3a;
      border-radius: 12px;
      padding: 14px 16px;
      font-size: 14px;
      color: #fff;
      outline: none;
      transition: border-color 0.2s;
    }
    input:focus { border-color: #f59e0b; }
    input::placeholder { color: #555; }
  </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center px-4">
  <div class="w-full max-w-sm">
    <div class="flex items-center justify-center gap-2 mb-10">
      <span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span>
      <span class="text-white text-xl font-medium tracking-wide">The Spot</span>
    </div>
    <div class="bg-[#222] rounded-2xl border border-[#2e2e2e] px-6 py-8">
      <p class="text-white text-base font-medium mb-1">Admin Panel</p>
      <p class="text-gray-500 text-sm mb-6">Sign in to manage your restaurant</p>
      <?php if ($error): ?>
      <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm rounded-xl px-4 py-3 mb-4">
        <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>
      <form method="POST" class="flex flex-col gap-4">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit"
          class="w-full bg-amber-400 text-[#1a1a1a] text-sm font-medium py-3.5 rounded-full hover:bg-amber-300 transition mt-1">
          Sign In
        </button>
      </form>
    </div>
    <p class="text-center text-gray-600 text-xs mt-6">Authorised personnel only.</p>
  </div>
</body>
</html>
