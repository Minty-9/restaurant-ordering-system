<?php
session_start();
if (!empty($_SESSION['staff_logged_in'])) {
    header("Location: index.php");
    exit;
}
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pin = $_POST['pin'] ?? '';
    if ($pin === '4321') {
        session_regenerate_id(true);
        $_SESSION['staff_logged_in'] = true;
        $_SESSION['staff_role'] = 'waiter';
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid PIN. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>The Spot — Staff Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { background: #1a1a1a; font-family: sans-serif; }
    input[type="password"] {
      width: 100%;
      background: #2a2a2a;
      border: 0.5px solid #3a3a3a;
      border-radius: 12px;
      padding: 14px 16px;
      font-size: 15px;
      color: #fff;
      outline: none;
      letter-spacing: 4px;
      transition: border-color 0.2s;
    }
    input[type="password"]:focus { border-color: #f59e0b; }
    input[type="password"]::placeholder { letter-spacing: 0; color: #555; }
  </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center px-4">

  <div class="w-full max-w-sm">

    <!-- Logo -->
    <div class="flex items-center justify-center gap-2 mb-10">
      <span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span>
      <span class="text-white text-xl font-medium tracking-wide">The Spot</span>
    </div>

    <!-- Card -->
    <div class="bg-[#222] rounded-2xl border border-[#2e2e2e] px-6 py-8">
      <p class="text-white text-base font-medium mb-1">Staff Access</p>
      <p class="text-gray-500 text-sm mb-6">Enter your PIN to continue</p>

      <?php if ($error): ?>
      <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm rounded-xl px-4 py-3 mb-4">
        <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <form method="POST" class="flex flex-col gap-4">
        <input type="password" name="pin" placeholder="Enter PIN" required maxlength="10">
        <button type="submit"
          class="w-full bg-amber-400 text-[#1a1a1a] text-sm font-medium py-3.5 rounded-full hover:bg-amber-300 transition">
          Login
        </button>
      </form>
    </div>

    <p class="text-center text-gray-600 text-xs mt-6">Staff only. Unauthorised access is prohibited.</p>
  </div>

</body>
</html>
