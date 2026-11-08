<?php
/**
 * index.php - Web Application Entry Point & Front Controller
 * Handles routing to RESTful API (/api/v1/... and /api/...) as well as Northwind BMS SPA Frontend
 */

$uri = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($uri, PHP_URL_PATH);

// 1. Direct API routes forwarding (supports /api/v1/..., /api/..., and ?route=...)
if (strpos($path, '/api') === 0 || strpos($path, '/v1') === 0 || isset($_GET['route']) || isset($_GET['endpoint'])) {
    require_once __DIR__ . '/api/index.php';
    exit;
}

// 2. Serve Single Page Application (Northwind BMS Frontend)
if (file_exists(__DIR__ . '/index.html')) {
    include __DIR__ . '/index.html';
} else {
    echo "Northwind BMS Application Ready";
}
