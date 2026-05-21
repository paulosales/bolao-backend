<?php
/**
 * PHP built-in server router script.
 * Usage: php -S 0.0.0.0:8080 router.php
 *
 * Serves static files from public/ directly; routes everything else
 * through public/index.php (Slim front-controller).
 */
$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . '/public' . $uri;

if (is_file($file)) {
    return false; // let the built-in server serve the file as-is
}

require __DIR__ . '/public/index.php';
