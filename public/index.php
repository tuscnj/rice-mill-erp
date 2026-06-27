<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
} elseif (file_exists($maintenance = __DIR__.'/../atik_erp_core/storage/framework/maintenance.php')) {
    require $maintenance;
}

// SMART PATH DETECTION: Choose paths based on the environment structure
if (file_exists(__DIR__.'/../atik_erp_core/vendor/autoload.php')) {
    // 1. LIVE CPANEL ENVIRONMENT
    require __DIR__.'/../atik_erp_core/vendor/autoload.php';
    $app = require_once __DIR__.'/../atik_erp_core/bootstrap/app.php';
} else {
    // 2. LOCAL CODESPACE ENVIRONMENT
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
}

// Handle the incoming request...
$app->handleRequest(Request::capture());