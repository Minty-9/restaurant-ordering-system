<?php
// Start session on every admin route
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_admin() {
    if (!isset($_SESSION['admin'])) {
        header("Location: index.php");
        exit;
    }
}


require_once "../src/auth.php";
require_admin();
