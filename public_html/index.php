<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader. Try multiple relative paths to
// accommodate different cPanel upload layouts (project above public_html
// or project inside public_html).
$autoloadPaths = [
    __DIR__.'/../vendor/autoload.php',
    __DIR__.'/../../vendor/autoload.php',
    __DIR__.'/vendor/autoload.php',
];

$loaded = false;
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require $path;
        $loaded = true;
        break;
    }
}

if (! $loaded) {
    http_response_code(500);
    echo 'Autoloader not found. Adjust paths in public_html/index.php to point to your vendor directory.';
    exit(1);
}

// Bootstrap Laravel and handle the request. Try multiple bootstrap paths.
$bootstrapPaths = [
    __DIR__.'/../bootstrap/app.php',
    __DIR__.'/../../bootstrap/app.php',
    __DIR__.'/bootstrap/app.php',
];

$app = null;
foreach ($bootstrapPaths as $bpath) {
    if (file_exists($bpath)) {
        $app = require_once $bpath;
        break;
    }
}

if (! $app) {
    http_response_code(500);
    echo 'Bootstrap app not found. Adjust paths in public_html/index.php to point to your bootstrap/app.php.';
    exit(1);
}

/** @var Application $app */
$app->handleRequest(Request::capture());
