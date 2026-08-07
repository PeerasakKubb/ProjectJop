<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

$appKey = $_ENV['APP_KEY'] ?? $_SERVER['APP_KEY'] ?? getenv('APP_KEY') ?: '';
$keyFile = __DIR__.'/../storage/app/render-app-key';

if (is_readable($keyFile)) {
    $fromFile = trim((string) file_get_contents($keyFile));
    $decodedFile = str_starts_with($fromFile, 'base64:')
        ? base64_decode(substr($fromFile, 7), true)
        : false;
    if ($decodedFile !== false && in_array(strlen($decodedFile), [16, 32], true)) {
        $appKey = $fromFile;
    }
}

$decodedKey = str_starts_with($appKey, 'base64:')
    ? base64_decode(substr($appKey, 7), true)
    : false;

if ($decodedKey === false || ! in_array(strlen($decodedKey), [16, 32], true)) {
    $appKey = 'base64:'.base64_encode(random_bytes(32));
    @mkdir(dirname($keyFile), 0777, true);
    @file_put_contents($keyFile, $appKey);
}

putenv("APP_KEY={$appKey}");
$_ENV['APP_KEY'] = $appKey;
$_SERVER['APP_KEY'] = $appKey;

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
