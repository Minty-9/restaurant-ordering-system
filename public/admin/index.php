<?php
ob_start();

require_once __DIR__ . '/../../src/bootstrap_sqlite.php';

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';

$page = $_GET['page'] ?? 'dashboard';

$allowedPages = [
    'dashboard',
    'items',
    'categories',
    'orders',
    'order_view'
];

if (!in_array($page, $allowedPages, true)) {
    $page = 'dashboard';
}

require __DIR__ . "/$page.php";

ob_end_flush();
