<?php
function route($path, $callback) {
    if ($_SERVER['REQUEST_URI'] === $path) {
        $callback();
        exit;
    }
}
