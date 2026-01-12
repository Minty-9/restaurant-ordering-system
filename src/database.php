<?php

try {
    $dbPath = sys_get_temp_dir() . '/restaurant.sqlite';

    $pdo = new PDO(
        'sqlite:' . $dbPath,
        null,
        null,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed.');
}
