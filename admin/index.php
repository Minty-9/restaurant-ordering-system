<?php
include "includes/auth.php"; // Protect all pages
include "includes/header.php";
include "includes/sidebar.php";

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

include "$page.php";
