<?php
session_start();

// If already logged in, go to staff dashboard
if (!empty($_SESSION['staff_logged_in'])) {
    header("Location: index.php");
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pin = $_POST['pin'] ?? '';

    // Portfolio-safe PIN login
    if ($pin === '4321') {
        session_regenerate_id(true);

        $_SESSION['staff_logged_in'] = true;
        $_SESSION['staff_role'] = 'waiter';

        // Redirect to staff dashboard
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid PIN";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="assets/css/login.css">
<title>Staff Login</title>
</head>
<body>

<div class="login-box">
    <h2>Staff Login</h2>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="password" name="pin" placeholder="Staff PIN" required>
        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>
