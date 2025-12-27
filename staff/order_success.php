<?php
$ref = $_GET['ref'] ?? null;
if (!$ref) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Placed</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="confirmation">
    <h1>Order Placed!</h1>
    <p>Your order reference is: <strong><?= htmlspecialchars($ref) ?></strong></p>
    <a href="index.php">Back to menu</a>
</div>
</body>
</html>
