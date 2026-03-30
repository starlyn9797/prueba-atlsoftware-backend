<?php

$uri  = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

$filePath = __DIR__ . $path;
if ($path !== '/' && file_exists($filePath) && is_file($filePath)) {
    return false;
}

require_once __DIR__ . '/index.php';
