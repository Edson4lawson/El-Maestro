<?php
// Server status check
header("Content-Type: application/json; charset=UTF-8");

echo json_encode([
    'status' => 'running',
    'timestamp' => date('Y-m-d H:i:s'),
    'server_info' => [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
        'port' => $_SERVER['SERVER_PORT'] ?? 'Unknown'
    ],
    'database_test' => 'accessible_via_separate_endpoint'
]);
?>
