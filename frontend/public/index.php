<?php
// PHP fallback for Vue.js SPA routing
// This file serves index.html for all Vue Router routes

$request_uri = $_SERVER['REQUEST_URI'];

// Check if this is an API request - don't interfere with API calls
if (strpos($request_uri, '/api/') !== false) {
    // Let the server handle API requests normally
    return false;
}

// Check if the requested file exists (CSS, JS, images, etc.)
$path = parse_url($request_uri, PHP_URL_PATH);
$file_path = __DIR__ . str_replace('/sn-progress', '', $path);

if (file_exists($file_path) && !is_dir($file_path)) {
    // File exists, let the server serve it normally
    return false;
}

// For all other routes (Vue Router routes), serve index.html
header('Content-Type: text/html; charset=utf-8');
readfile(__DIR__ . '/index.html');
?>