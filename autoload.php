<?php

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix    = 'App\\';
    $baseDir   = __DIR__ . '/src/';
    $prefixLen = strlen($prefix);

    if (strncmp($prefix, $class, $prefixLen) !== 0) {
        return;
    }

    $relativeClass = substr($class, $prefixLen);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
