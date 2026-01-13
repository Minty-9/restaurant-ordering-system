<?php

$dbPath = __DIR__ . '/../database/database.sqlite';

if (!file_exists($dbPath)) {
    die('SQLite DB missing: ' . $dbPath);
}

$pdo = new PDO(
    'sqlite:' . $dbPath,
    null,
    null,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);
