<?php

try {
    $pdo = new PDO(
        'sqlite:' . sys_get_temp_dir() . '/restaurant.sqlite',
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
