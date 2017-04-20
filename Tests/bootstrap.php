<?php

$paths = [
    __DIR__ . '/app/cache/container.php',
    __DIR__ . '/app/cache/db.sqlite',
];

foreach ($paths as $path) {
    if (is_file($path)) {
        unlink($path);
    }
}

require __DIR__ . '/../vendor/autoload.php';