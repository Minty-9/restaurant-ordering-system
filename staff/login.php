<?php
session_start();
$error = null;

// Simple PIN login (perfect for portfolio)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['pin'] === '4321') {

        session_regenerate_id(true);

        $_SESSION['staff_logged_in'] = true;
        $_SESSION['staff_role'] = 'waiter'; // or kitchen
        header("Location: index.php");
        exit;
    }
    $error = "Invalid PIN";
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
<?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>

<form method="POST">
<input type="password" name="pin" placeholder="Staff PIN" required>
<button>Login</button>
</form>
</div>

</body>
</html>
