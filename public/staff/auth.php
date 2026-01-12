<?php
session_start();

if (!isset($_SESSION['staff_logged_in'])) {
    header("Location: login.php");
    exit;
}
